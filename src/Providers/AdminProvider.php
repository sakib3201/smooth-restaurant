<?php

/**
 * Admin service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;

/**
 * Class AdminProvider
 *
 * Hooks admin screens in boot() on wp-admin requests only.
 * Shell: menu/screen wiring lands with the admin follow-up issue.
 */
final class AdminProvider extends ServiceProvider
{
    /**
     * Register services with the container.
     *
     * Bind-only: no hooks, no database access, no translation calls.
     * Shell: no admin bindings yet.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function register(Container $container): void
    {
    }

    /**
     * Boot the provider after all providers are registered.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function boot(Container $container): void
    {
        if (! $this->isAdmin()) {
            return;
        }

        add_action('admin_menu', array( $this, 'registerMenu' ));
        $this->markBooted();
    }

    /**
     * Register admin menu screens.
     *
     * Shell: real menu wiring lands with the admin follow-up issue.
     *
     * @return void
     */
    public function registerMenu(): void
    {
    }
}
