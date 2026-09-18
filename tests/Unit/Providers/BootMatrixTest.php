<?php

/**
 * Boot-matrix smoke test.
 *
 * Verifies the spec boot matrix behaviorally: context-gated registration
 * (Plugin::registerProviders() filters by Context::current() before
 * instantiation) plus in-context boot():
 *
 * - Diner menu (frontend): Core + Database + Assets + Menu + Cart +
 *   Checkout + Orders + Payments + Slots + Reservations + Tables + Blocks
 *   register; Admin, Rest, and Notifications (cron worker) are excluded.
 * - Admin: Core + Database + Assets + Menu (capability map) + Admin +
 *   Blocks register; diner providers, Rest, and Notifications are excluded.
 * - Cron: Core + Database + Notifications register; everything else is
 *   excluded.
 * - REST: covered by RestBootTest (REST_REQUEST is a process-global constant).
 *
 * A non-Smooth frontend request still enqueues zero Smooth assets.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Context;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Providers\AdminProvider;
use SmoothRestaurant\Providers\AssetsProvider;
use SmoothRestaurant\Providers\BlocksProvider;
use SmoothRestaurant\Providers\CartProvider;
use SmoothRestaurant\Providers\CheckoutProvider;
use SmoothRestaurant\Providers\CoreProvider;
use SmoothRestaurant\Providers\DatabaseProvider;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\NotificationsProvider;
use SmoothRestaurant\Providers\OrdersProvider;
use SmoothRestaurant\Providers\PaymentsProvider;
use SmoothRestaurant\Providers\ReservationsProvider;
use SmoothRestaurant\Providers\RestProvider;
use SmoothRestaurant\Providers\SlotsProvider;
use SmoothRestaurant\Providers\TablesProvider;

/**
 * Class BootMatrixTest
 */
class BootMatrixTest extends TestCase
{
    /**
     * Set up a clean plugin singleton and stub state for each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Plugin::reset();
        Context::reset();
        sr_test_reset_stubs();
    }

    /**
     * Tear down stub state and the plugin singleton after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        sr_test_reset_stubs();
        Context::reset();
        Plugin::reset();
        parent::tearDown();
    }

    /**
     * Boot a fresh plugin and index providers by class.
     *
     * Clears hooks and enqueues but preserves the context flags the test
     * set (is_admin / doing_cron): a full stub reset would wipe them
     * before boot and every context matrix would read as frontend.
     *
     * @return array<string, ServiceProvider> Provider instances keyed by class name.
     */
    private function bootFreshPlugin(): array
    {
        Plugin::reset();
        $GLOBALS['__sr_test_hooks']    = array(
            'actions' => array(),
            'filters' => array(),
        );
        $GLOBALS['__sr_test_enqueues'] = array();
        Plugin::instance()->boot();

        $indexed = array();
        foreach (Plugin::instance()->container()->providers() as $provider) {
            $indexed[ $provider::class ] = $provider;
        }

        return $indexed;
    }

    /**
     * Assert a provider registered and booted (or bailed) in the scenario.
     *
     * @param array<string, ServiceProvider> $providers Providers keyed by class.
     * @param string                         $class     Provider class.
     * @param bool                           $expected  Expected booted() value.
     * @return void
     */
    private function assertBooted(array $providers, string $class, bool $expected): void
    {
        $this->assertArrayHasKey($class, $providers, sprintf('%s should be registered.', $class));
        $this->assertSame(
            $expected,
            $providers[ $class ]->booted(),
            sprintf('%s booted() should be %s.', $class, $expected ? 'true' : 'false')
        );
    }

    /**
     * Assert a provider was excluded by context-gated registration.
     *
     * @param array<string, ServiceProvider> $providers Providers keyed by class.
     * @param string                         $class     Provider class.
     * @return void
     */
    private function assertExcluded(array $providers, string $class): void
    {
        $this->assertArrayNotHasKey($class, $providers, sprintf('%s should be excluded by context.', $class));
        $this->assertNotContains($class, Plugin::instance()->providerClasses());
    }

    /**
     * Test the diner menu matrix on a frontend request.
     *
     * @return void
     */
    public function test_diner_menu_boots_on_frontend(): void
    {
        $providers = $this->bootFreshPlugin();

        $this->assertSame(
            array(
                CoreProvider::class,
                DatabaseProvider::class,
                AssetsProvider::class,
                MenuProvider::class,
                CartProvider::class,
                CheckoutProvider::class,
                OrdersProvider::class,
                PaymentsProvider::class,
                SlotsProvider::class,
                ReservationsProvider::class,
                TablesProvider::class,
                BlocksProvider::class,
            ),
            Plugin::instance()->providerClasses()
        );

        foreach (
            array(
                CoreProvider::class,
                MenuProvider::class,
                CartProvider::class,
                CheckoutProvider::class,
                OrdersProvider::class,
                PaymentsProvider::class,
                SlotsProvider::class,
                ReservationsProvider::class,
                TablesProvider::class,
                BlocksProvider::class,
            ) as $class
        ) {
            $this->assertBooted($providers, $class, true);
        }

        $this->assertExcluded($providers, AdminProvider::class);
        $this->assertExcluded($providers, RestProvider::class);
        $this->assertExcluded($providers, NotificationsProvider::class);
    }

    /**
     * Test that a non-Smooth frontend request enqueues zero assets.
     *
     * @return void
     */
    public function test_frontend_enqueues_zero_assets(): void
    {
        $this->bootFreshPlugin();

        $this->assertSame(array(), $GLOBALS['__sr_test_enqueues']);
    }

    /**
     * Test the admin matrix.
     *
     * @return void
     */
    public function test_admin_boots_admin_provider_only(): void
    {
        sr_test_set_flag('is_admin', true);
        $providers = $this->bootFreshPlugin();

        $this->assertSame(
            array(
                CoreProvider::class,
                DatabaseProvider::class,
                AssetsProvider::class,
                MenuProvider::class,
                AdminProvider::class,
                BlocksProvider::class,
            ),
            Plugin::instance()->providerClasses()
        );

        $this->assertBooted($providers, AdminProvider::class, true);
        $this->assertBooted($providers, MenuProvider::class, true);
        $this->assertBooted($providers, CoreProvider::class, true);

        foreach (
            array(
                CartProvider::class,
                RestProvider::class,
                NotificationsProvider::class,
            ) as $class
        ) {
            $this->assertExcluded($providers, $class);
        }
    }

    /**
     * Test the cron matrix: only the Notifications worker registers.
     *
     * @return void
     */
    public function test_cron_boots_notifications_worker_only(): void
    {
        sr_test_set_flag('doing_cron', true);
        $providers = $this->bootFreshPlugin();

        $this->assertSame(
            array(
                CoreProvider::class,
                DatabaseProvider::class,
                NotificationsProvider::class,
            ),
            Plugin::instance()->providerClasses()
        );

        $this->assertBooted($providers, NotificationsProvider::class, true);
        $this->assertBooted($providers, CoreProvider::class, true);

        foreach (
            array(
                MenuProvider::class,
                CartProvider::class,
                AdminProvider::class,
                RestProvider::class,
                AssetsProvider::class,
            ) as $class
        ) {
            $this->assertExcluded($providers, $class);
        }
    }
}
