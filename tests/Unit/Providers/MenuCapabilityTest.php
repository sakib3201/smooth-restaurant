<?php

/**
 * Unit tests for the menu management capability.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\Context;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\RestProvider;

/**
 * Class MenuCapabilityTest
 *
 * smooth_manage_menus maps to manage_options through a map_meta_cap filter
 * (no role writes, multisite-correct), and the REST permission callback
 * denies without the cap and allows with it.
 */
class MenuCapabilityTest extends TestCase
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
     * Boot MenuProvider in isolation.
     *
     * @return MenuProvider
     */
    private function bootMenuProvider(): MenuProvider
    {
        $container = new Container();
        $container->register(MenuProvider::class);
        $container->boot();

        $provider = $container->providers()[0];
        $this->assertInstanceOf(MenuProvider::class, $provider);

        return $provider;
    }

    /**
     * Test that boot hooks the capability map and marks booted.
     *
     * @return void
     */
    public function test_boot_hooks_capability_map(): void
    {
        $provider = $this->bootMenuProvider();

        $this->assertTrue($provider->booted());
        $this->assertNotFalse(has_filter('map_meta_cap'));
        $this->assertNotFalse(has_action('init'));
    }

    /**
     * Test that boot bails on cron (no hooks, not booted).
     *
     * @return void
     */
    public function test_boot_bails_on_cron(): void
    {
        sr_test_set_flag('doing_cron', true);
        $provider = $this->bootMenuProvider();

        $this->assertFalse($provider->booted());
        $this->assertFalse(has_filter('map_meta_cap'));
    }

    /**
     * Test that the filter maps smooth_manage_menus to manage_options.
     *
     * @return void
     */
    public function test_capability_maps_to_manage_options(): void
    {
        $this->bootMenuProvider();

        $mapped = apply_filters(
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core capability filter under test.
            'map_meta_cap',
            array( 'do_not_allow' ),
            MenuProvider::MANAGE_CAP,
            1,
            array()
        );

        $this->assertSame(array( 'manage_options' ), $mapped);
    }

    /**
     * Test that unrelated capabilities pass through untouched.
     *
     * @return void
     */
    public function test_other_capabilities_pass_through(): void
    {
        $this->bootMenuProvider();

        $mapped = apply_filters(
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core capability filter under test.
            'map_meta_cap',
            array( 'edit_posts' ),
            'edit_posts',
            1,
            array()
        );

        $this->assertSame(array( 'edit_posts' ), $mapped);
    }

    /**
     * Test that the management permission callback denies without the cap.
     *
     * @return void
     */
    public function test_management_callback_denies_without_cap(): void
    {
        $callback = RestProvider::capability(MenuProvider::MANAGE_CAP);

        $this->assertFalse($callback());
    }

    /**
     * Test that the management permission callback allows with the cap.
     *
     * @return void
     */
    public function test_management_callback_allows_with_cap(): void
    {
        sr_test_grant_caps(array( MenuProvider::MANAGE_CAP ));
        $callback = RestProvider::capability(MenuProvider::MANAGE_CAP);

        $this->assertTrue($callback());
    }
}
