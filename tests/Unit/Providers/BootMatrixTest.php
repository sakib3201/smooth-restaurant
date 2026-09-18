<?php

/**
 * Boot-matrix smoke test.
 *
 * Verifies the spec boot matrix behaviorally: which providers boot in each
 * request context, and that a non-Smooth frontend request enqueues zero
 * Smooth assets.
 *
 * - Diner menu (frontend): Core + Menu + Cart + Checkout + Orders +
 *   Payments + Slots + Reservations + Tables + Blocks boot; Admin, Rest,
 *   and Notifications (cron worker) bail.
 * - Admin: Admin boots; diner providers bail.
 * - Cron: Notifications boots; everything else except Core bails.
 * - REST: covered by RestBootTest (REST_REQUEST is a process-global constant).
 *
 * Database/Assets providers are owned by the parallel platform stream: this
 * test tolerates them as either registered (post-merge) or recorded in
 * skippedProviders() (pre-merge), never as a fatal.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Providers\AdminProvider;
use SmoothRestaurant\Providers\BlocksProvider;
use SmoothRestaurant\Providers\CartProvider;
use SmoothRestaurant\Providers\CheckoutProvider;
use SmoothRestaurant\Providers\CoreProvider;
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
     * Diner-facing providers expected to boot on a frontend request.
     *
     * @var array<int, class-string>
     */
    private const DINER_PROVIDERS = array(
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
    );

    /**
     * Set up a clean plugin singleton and stub state for each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Plugin::reset();
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
     * @return array<string, object> Provider instances keyed by class name.
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
     * Assert a provider booted (or bailed) in the current scenario.
     *
     * @param array<string, object> $providers Providers keyed by class.
     * @param string                $class     Provider class.
     * @param bool                  $expected  Expected booted() value.
     * @return void
     */
    private function assertBooted(array $providers, string $class, bool $expected): void
    {
        $this->assertArrayHasKey($class, $providers, sprintf('%s should be registered.', $class));
        assert($providers[ $class ] instanceof ServiceProvider);
        $this->assertSame(
            $expected,
            $providers[ $class ]->booted(),
            sprintf('%s booted() should be %s.', $class, $expected ? 'true' : 'false')
        );
    }

    /**
     * Test the diner menu matrix on a frontend request.
     *
     * @return void
     */
    public function test_diner_menu_boots_on_frontend(): void
    {
        $providers = $this->bootFreshPlugin();

        foreach (self::DINER_PROVIDERS as $class) {
            $this->assertBooted($providers, $class, true);
        }

        $this->assertBooted($providers, AdminProvider::class, false);
        $this->assertBooted($providers, RestProvider::class, false);
        $this->assertBooted($providers, NotificationsProvider::class, false);
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

        $this->assertBooted($providers, AdminProvider::class, true);
        $this->assertBooted($providers, CoreProvider::class, true);
        $absent = array( MenuProvider::class, BlocksProvider::class, CartProvider::class );
        $absent[] = RestProvider::class;
        $absent[] = NotificationsProvider::class;
        foreach ($absent as $class) {
            $this->assertBooted($providers, $class, false);
        }
    }

    /**
     * Test the cron matrix: only the Notifications worker boots.
     *
     * @return void
     */
    public function test_cron_boots_notifications_worker_only(): void
    {
        sr_test_set_flag('doing_cron', true);
        $providers = $this->bootFreshPlugin();

        $this->assertBooted($providers, NotificationsProvider::class, true);
        $this->assertBooted($providers, CoreProvider::class, true);
        $absent = array( MenuProvider::class, AdminProvider::class, RestProvider::class, CartProvider::class );
        foreach ($absent as $class) {
            $this->assertBooted($providers, $class, false);
        }
    }

    /**
     * Test that platform-stream providers never fatal the boot.
     *
     * @return void
     */
    public function test_platform_providers_tolerated_pre_merge(): void
    {
        $this->bootFreshPlugin();

        $registered = Plugin::instance()->providerClasses();
        $skipped    = Plugin::instance()->skippedProviders();

        foreach (
            array(
                'SmoothRestaurant\\Providers\\DatabaseProvider',
                'SmoothRestaurant\\Providers\\AssetsProvider',
            ) as $class
        ) {
            $this->assertTrue(
                in_array($class, $registered, true) || array_key_exists($class, $skipped),
                sprintf('%s should be registered or recorded as skipped, never fatal.', $class)
            );
        }
    }
}
