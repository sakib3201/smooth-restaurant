<?php

/**
 * Unit tests for the provider lifecycle.
 *
 * Covers: register() adds no hooks, boot() bails outside its context, and
 * invalid Pro entries via the `smooth_restaurant_service_providers` filter are skipped
 * and logged while boot continues.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Core\TestableService;
use SmoothRestaurant\Providers\AdminProvider;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\NotificationsProvider;
use SmoothRestaurant\Providers\RestProvider;

/**
 * Class ProviderLifecycleTest
 */
class ProviderLifecycleTest extends TestCase
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
        sr_test_reset_stubs();
    }

    /**
     * Tear down stub filters and the plugin singleton after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        remove_filter('smooth_restaurant_service_providers');
        sr_test_reset_stubs();
        Plugin::reset();
        parent::tearDown();
    }

    /**
     * Test that register() binds services without adding hooks.
     *
     * @return void
     */
    public function test_register_adds_no_hooks(): void
    {
        $container = new Container();
        $container->register(LifecycleHookProvider::class);

        $this->assertFalse(has_action('smooth_lifecycle_probe'));
        $this->assertTrue($container->has(TestableService::class));
    }

    /**
     * Test that boot() registers hooks once all providers are registered.
     *
     * @return void
     */
    public function test_boot_adds_hooks_after_register(): void
    {
        $container = new Container();
        $container->register(LifecycleHookProvider::class);
        $container->boot();

        $this->assertNotFalse(has_action('smooth_lifecycle_probe'));
    }

    /**
     * Test that context-gated providers bail outside their context.
     *
     * @return void
     */
    public function test_boot_bails_outside_context(): void
    {
        $container = new Container();
        foreach (array( AdminProvider::class, RestProvider::class, NotificationsProvider::class ) as $class) {
            $container->register($class);
        }
        $container->boot();

        foreach ($container->providers() as $provider) {
            $this->assertFalse(
                $provider->booted(),
                sprintf('%s should bail on a diner frontend request.', $provider::class)
            );
        }

        $this->assertFalse(has_action('smooth_admin_probe'));
        $this->assertFalse(has_action('smooth_rest_probe'));
        $this->assertFalse(has_action('smooth_notify_probe'));
    }

    /**
     * Test that a diner provider boots in context on the frontend.
     *
     * @return void
     */
    public function test_diner_provider_boots_on_frontend(): void
    {
        $container = new Container();
        $container->register(MenuProvider::class);
        $container->boot();

        $this->assertContains(MenuProvider::class, $container->providerClasses());
        foreach ($container->providers() as $provider) {
            $this->assertTrue($provider->booted());
        }
    }

    /**
     * Test that invalid Pro entries are skipped, logged, and do not stop boot.
     *
     * @return void
     */
    public function test_invalid_pro_entries_skipped_and_logged(): void
    {
        add_filter(
            'smooth_restaurant_service_providers',
            static fn (array $list): array => array_merge(
                $list,
                array(
                    'SmoothRestaurant\\DoesNotExist\\MissingProProvider',
                    NotAProvider::class,
                    StaleProProvider::class,
                )
            )
        );

        Plugin::instance()->boot();

        $skipped = Plugin::instance()->skippedProviders();
        $this->assertArrayHasKey('SmoothRestaurant\\DoesNotExist\\MissingProProvider', $skipped);
        $this->assertArrayHasKey(NotAProvider::class, $skipped);
        $this->assertArrayHasKey(StaleProProvider::class, $skipped);
        foreach ($skipped as $reason) {
            $this->assertNotSame('', $reason);
        }

        // Boot continues for valid providers despite the invalid entries.
        $this->assertContains(MenuProvider::class, Plugin::instance()->providerClasses());
    }

    /**
     * Test that a valid Pro provider is additive: registered and booted.
     *
     * @return void
     */
    public function test_valid_pro_provider_registers_and_boots(): void
    {
        add_filter(
            'smooth_restaurant_service_providers',
            static fn (array $list): array => array_merge($list, array( LifecycleProProvider::class ))
        );

        Plugin::instance()->boot();

        $this->assertContains(LifecycleProProvider::class, Plugin::instance()->providerClasses());
        $this->assertArrayNotHasKey(LifecycleProProvider::class, Plugin::instance()->skippedProviders());
        foreach (Plugin::instance()->container()->providers() as $provider) {
            if ($provider instanceof LifecycleProProvider) {
                $this->assertTrue($provider->booted());
            }
        }
    }

    /**
     * Test that removing a Free provider via the filter is ignored.
     *
     * @return void
     */
    public function test_free_provider_removal_is_ignored(): void
    {
        add_filter(
            'smooth_restaurant_service_providers',
            static fn (array $list): array => array_values(
                array_filter($list, static fn (string $class): bool => $class !== MenuProvider::class)
            )
        );

        Plugin::instance()->boot();

        $this->assertContains(MenuProvider::class, Plugin::instance()->providerClasses());
        $this->assertArrayHasKey(MenuProvider::class, Plugin::instance()->skippedProviders());
    }
}

/**
 * Fixture: provider that binds in register() and hooks in boot().
 */
class LifecycleHookProvider extends ServiceProvider
{
    /**
     * Register services with the container.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function register(Container $container): void
    {
        $container->singleton(TestableService::class);
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
        add_action('smooth_lifecycle_probe', static function (): void {
        });
    }
}

/**
 * Fixture: additive Pro provider that always boots in context.
 */
class LifecycleProProvider extends ServiceProvider
{
    /**
     * Register services with the container.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function register(Container $container): void
    {
        $container->singleton(TestableService::class);
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
        add_action('smooth_pro_probe', static function (): void {
        });
    }
}

/**
 * Fixture: Pro provider below the version floor.
 */
class StaleProProvider extends LifecycleProProvider
{
    /**
     * Outdated provider version.
     *
     * @var string
     */
    public const VERSION = '0.0.1';
}

/**
 * Fixture: class that does not extend ServiceProvider.
 */
class NotAProvider
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }
}
