<?php

/**
 * REST service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;

/**
 * Class RestProvider
 *
 * Hooks REST route registration in boot() on REST requests only.
 * Shell: route wiring lands with the REST follow-up issue.
 */
final class RestProvider extends ServiceProvider
{
    /**
     * REST API namespace for all Smooth routes.
     */
    public const NAMESPACE = 'smooth/v1';

    /**
     * Request contexts this provider participates in.
     *
     * Mirrors boot(): REST requests only.
     *
     * @return list<string>
     */
    public static function contexts(): array
    {
        return array( 'rest' );
    }

    /**
     * Build a namespaced route path.
     *
     * Trims surrounding slashes so `route('orders')`, `route('/orders')`,
     * and `route('/orders/')` all resolve to `smooth/v1/orders`. An empty
     * path returns the bare namespace.
     *
     * @param string $path Route path fragment.
     * @return string Namespaced route path.
     */
    public static function route(string $path): string
    {
        $trimmed = trim($path, '/');
        if ('' === $trimmed) {
            return self::NAMESPACE;
        }

        return self::NAMESPACE . '/' . $trimmed;
    }

    /**
     * Build a REST permission callback for the given capability.
     *
     * Returns true when the capability API is unavailable: unit tests run
     * without WordPress, where no user/capability system exists, so the
     * callback must allow the request for routes to stay testable.
     * Production always has `current_user_can()`, so the closure enforces
     * the capability there.
     *
     * @param string $cap Required capability.
     * @return callable Permission callback returning bool.
     */
    public static function capability(string $cap): callable
    {
        return self::requireCapability($cap);
    }

    /**
     * Internal worker behind capability().
     *
     * @param string $cap Required capability.
     * @return callable Permission callback returning bool.
     */
    protected static function requireCapability(string $cap): callable
    {
        return static function (mixed ...$args) use ($cap): bool {
            if (! function_exists('current_user_can')) {
                return true;
            }

            return current_user_can($cap, ...$args);
        };
    }

    /**
     * Register services with the container.
     *
     * Bind-only: no hooks, no database access, no translation calls.
     * Shell: no REST bindings yet.
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
        if (! $this->isDoingRest()) {
            return;
        }

        add_action('rest_api_init', array( $this, 'registerRoutes' ));
        $this->markBooted();
    }

    /**
     * Register REST routes.
     *
     * Shell: real route wiring lands with the REST follow-up issue.
     *
     * @return void
     */
    public function registerRoutes(): void
    {
    }
}
