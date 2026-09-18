<?php

/**
 * Core service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;

/**
 * Class CoreProvider
 *
 * Boots on every request context (no early bail): owns plugin-wide
 * bindings and i18n. Shell: domain bindings land with follow-up issues.
 */
final class CoreProvider extends ServiceProvider
{
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
        // Shell: no core bindings yet. The container self-binding happens
        // in Container::register(); domain follow-ups add bindings here.
    }

    /**
     * Boot the provider after all providers are registered.
     *
     * Core has no request context to bail on: it boots everywhere.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function boot(Container $container): void
    {
        add_action('init', array( $this, 'loadTextdomain' ), 1);
        $this->markBooted();
    }

    /**
     * Load plugin translations.
     *
     * Shell: load_plugin_textdomain() wiring lands with the i18n pass.
     *
     * @return void
     */
    public function loadTextdomain(): void
    {
    }
}
