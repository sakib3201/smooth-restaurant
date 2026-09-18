<?php

/**
 * Unit tests for context-gated provider registration.
 *
 * Covers Context::current() resolution (flags, precedence, test override)
 * and Plugin::registerProviders() filtering the final list by context
 * before instantiation: Free list and Pro appends alike.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\Context;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Providers\AdminProvider;
use SmoothRestaurant\Providers\CartProvider;
use SmoothRestaurant\Providers\CoreProvider;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\NotificationsProvider;
use SmoothRestaurant\Providers\RestProvider;

/**
 * Class ContextFilterTest
 */
class ContextFilterTest extends TestCase
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
     * Tear down stub filters and the plugin singleton after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        remove_filter('smooth_service_providers');
        sr_test_reset_stubs();
        Context::reset();
        Plugin::reset();
        parent::tearDown();
    }

    /**
     * Test that the frontend context excludes admin/REST/cron providers.
     *
     * @return void
     */
    public function test_frontend_excludes_admin_rest_and_cron_providers(): void
    {
        Plugin::instance()->boot();

        $classes = Plugin::instance()->providerClasses();
        $this->assertContains(MenuProvider::class, $classes);
        $this->assertNotContains(AdminProvider::class, $classes);
        $this->assertNotContains(RestProvider::class, $classes);
        $this->assertNotContains(NotificationsProvider::class, $classes);
    }

    /**
     * Test that the admin context excludes diner providers except Menu.
     *
     * MenuProvider registers in admin for the capability map (editor
     * bindings + management routes); every other diner provider stays out.
     *
     * @return void
     */
    public function test_admin_excludes_diner_providers_except_menu(): void
    {
        sr_test_set_flag('is_admin', true);
        Plugin::instance()->boot();

        $classes = Plugin::instance()->providerClasses();
        $this->assertContains(AdminProvider::class, $classes);
        $this->assertContains(CoreProvider::class, $classes);
        $this->assertContains(MenuProvider::class, $classes);
        $this->assertNotContains(CartProvider::class, $classes);
        $this->assertNotContains(RestProvider::class, $classes);
        $this->assertNotContains(NotificationsProvider::class, $classes);
    }

    /**
     * Test that the cron context registers only the cron worker (+ always-on).
     *
     * @return void
     */
    public function test_cron_registers_notifications_worker_only(): void
    {
        sr_test_set_flag('doing_cron', true);
        Plugin::instance()->boot();

        $classes = Plugin::instance()->providerClasses();
        $this->assertContains(NotificationsProvider::class, $classes);
        $this->assertContains(CoreProvider::class, $classes);
        $this->assertNotContains(MenuProvider::class, $classes);
        $this->assertNotContains(AdminProvider::class, $classes);
        $this->assertNotContains(RestProvider::class, $classes);
    }

    /**
     * Test the REST context via the test override (no process-global constant).
     *
     * MenuProvider registers in REST for the capability map behind the
     * management routes; every other diner/admin/cron provider stays out.
     *
     * @return void
     */
    public function test_rest_context_registers_rest_and_menu_providers(): void
    {
        Context::override(Context::REST);
        Plugin::instance()->boot();

        $classes = Plugin::instance()->providerClasses();
        $this->assertContains(RestProvider::class, $classes);
        $this->assertContains(CoreProvider::class, $classes);
        $this->assertContains(MenuProvider::class, $classes);
        $this->assertNotContains(AdminProvider::class, $classes);
        $this->assertNotContains(NotificationsProvider::class, $classes);
    }

    /**
     * Test the WP-CLI context via the test override.
     *
     * @return void
     */
    public function test_cli_context_excludes_diner_admin_rest_and_cron_providers(): void
    {
        Context::override(Context::CLI);
        Plugin::instance()->boot();

        $classes = Plugin::instance()->providerClasses();
        $this->assertContains(CoreProvider::class, $classes);
        $this->assertNotContains(MenuProvider::class, $classes);
        $this->assertNotContains(AdminProvider::class, $classes);
        $this->assertNotContains(RestProvider::class, $classes);
        $this->assertNotContains(NotificationsProvider::class, $classes);
    }

    /**
     * Test that context filtering applies to Pro appends, with a recorded reason.
     *
     * @return void
     */
    public function test_context_filtering_applies_to_pro_appends(): void
    {
        add_filter(
            'smooth_service_providers',
            static fn (array $list): array => array_merge($list, array( ContextAdminProProvider::class ))
        );

        Plugin::instance()->boot();

        $this->assertNotContains(ContextAdminProProvider::class, Plugin::instance()->providerClasses());
        $skipped = Plugin::instance()->skippedProviders();
        $this->assertArrayHasKey(ContextAdminProProvider::class, $skipped);
        $this->assertStringStartsWith('context-filtered:', $skipped[ ContextAdminProProvider::class ]);
    }

    /**
     * Test that the filtered Pro provider registers in its own context.
     *
     * @return void
     */
    public function test_filtered_pro_provider_registers_in_its_context(): void
    {
        add_filter(
            'smooth_service_providers',
            static fn (array $list): array => array_merge($list, array( ContextAdminProProvider::class ))
        );
        sr_test_set_flag('is_admin', true);

        Plugin::instance()->boot();

        $this->assertContains(ContextAdminProProvider::class, Plugin::instance()->providerClasses());
        $this->assertArrayNotHasKey(ContextAdminProProvider::class, Plugin::instance()->skippedProviders());
    }

    /**
     * Test Context::current() defaults to frontend without WordPress flags.
     *
     * @return void
     */
    public function test_current_defaults_to_frontend(): void
    {
        $this->assertSame(Context::FRONTEND, Context::current());
    }

    /**
     * Test Context::current() reads the admin and cron flags.
     *
     * @return void
     */
    public function test_current_reads_admin_and_cron_flags(): void
    {
        sr_test_set_flag('is_admin', true);
        $this->assertSame(Context::ADMIN, Context::current());

        sr_test_set_flag('doing_cron', true);
        $this->assertSame(Context::CRON, Context::current());
    }

    /**
     * Test that the test override wins and reset clears it.
     *
     * @return void
     */
    public function test_override_wins_and_reset_clears(): void
    {
        sr_test_set_flag('is_admin', true);

        Context::override(Context::CLI);
        $this->assertSame(Context::CLI, Context::current());

        Context::reset();
        $this->assertSame(Context::ADMIN, Context::current());
    }
}

/**
 * Fixture: additive Pro provider that participates in admin only.
 */
class ContextAdminProProvider extends ServiceProvider
{
    /**
     * Request contexts this provider participates in.
     *
     * @return list<string>
     */
    public static function contexts(): array
    {
        return array( 'admin' );
    }

    /**
     * Register services with the container.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function register(Container $container): void
    {
    }

    /**
     * Boot the provider.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function boot(Container $container): void
    {
        $this->markBooted();
    }
}
