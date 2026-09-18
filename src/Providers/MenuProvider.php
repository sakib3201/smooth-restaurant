<?php

/**
 * Menu service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Contracts\MenuItemRepositoryInterface;
use SmoothRestaurant\Contracts\MenuRepositoryInterface;
use SmoothRestaurant\Contracts\ModifierRepositoryInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Database\Repositories\MenuItemRepository;
use SmoothRestaurant\Database\Repositories\MenuRepository;
use SmoothRestaurant\Database\Repositories\ModifierRepository;
use SmoothRestaurant\Domains\Menu\MenuService;

/**
 * Class MenuProvider
 *
 * Binds menu services in register() (bind-only) and hooks menu rendering
 * plus the menu capability map in boot(). Frontend renders menus; admin and
 * REST need the capability map (editor bindings, management routes).
 */
final class MenuProvider extends ServiceProvider
{
    /**
     * Capability guarding menu management writes.
     */
    public const MANAGE_CAP = 'smooth_manage_menus';

    /**
     * Request contexts this provider participates in.
     *
     * Mirrors boot(): everything except cron/CLI. Frontend renders menus;
     * admin and REST need the capability map.
     *
     * @return list<string>
     */
    public static function contexts(): array
    {
        return array( 'frontend', 'admin', 'rest' );
    }

    /**
     * Register services with the container.
     *
     * Bind-only: no hooks, no database access, no translation calls.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function register(Container $container): void
    {
        $container->singleton(MenuService::class);
        $container->singleton(MenuRepositoryInterface::class, MenuRepository::class);
        $container->singleton(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $container->singleton(ModifierRepositoryInterface::class, ModifierRepository::class);
    }

    /**
     * Boot the provider after all providers are registered.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function boot(Container $container): void
    {
        if ($this->isDoingCron()) {
            return;
        }

        add_action('init', array( $this, 'registerPostType' ));
        add_filter('map_meta_cap', array( $this, 'mapMenuCaps' ), 10, 4);
        $this->markBooted();
    }

    /**
     * Register the menu post type mirror.
     *
     * Shell: real registration lands with the menu domain issue.
     *
     * @return void
     */
    public function registerPostType(): void
    {
    }

    /**
     * Map the menu management capability onto manage_options.
     *
     * No role writes: multisite keeps per-site manage_options semantics and
     * the mapping stays testable through the apply_filters stub.
     *
     * @param list<string> $caps    Primitive capabilities required.
     * @param string       $cap     Capability being checked.
     * @param int          $userId  User id being checked.
     * @param list<string> $args    Additional check arguments.
     * @return list<string> Mapped primitive capabilities.
     */
    public function mapMenuCaps(array $caps, string $cap, int $userId = 0, array $args = array()): array
    {
        if (self::MANAGE_CAP === $cap) {
            return array( 'manage_options' );
        }

        return $caps;
    }
}
