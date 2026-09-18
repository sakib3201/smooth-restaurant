<?php

/**
 * Menu service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Domains\Menu\MenuService;

/**
 * Class MenuProvider
 *
 * Binds menu services in register() (bind-only) and hooks menu
 * rendering in boot() on diner frontend requests only.
 */
final class MenuProvider extends ServiceProvider
{
    /**
     * Request contexts this provider participates in.
     *
     * Mirrors boot(): diner frontend requests only.
     *
     * @return list<string>
     */
    public static function contexts(): array
    {
        return array( 'frontend' );
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
    }

    /**
     * Boot the provider after all providers are registered.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function boot(Container $container): void
    {
        if ($this->isBackendRequest()) {
            return;
        }

        add_action('init', array( $this, 'registerPostType' ));
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
}
