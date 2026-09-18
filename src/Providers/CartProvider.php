<?php

/**
 * Cart service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Contracts\CartRepositoryInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Database\Repositories\CartRepository;
use SmoothRestaurant\Domains\Cart\CartService;

/**
 * Class CartProvider
 *
 * Binds cart services in register() (bind-only) and hooks cart session
 * handling in boot() on diner frontend requests only.
 */
final class CartProvider extends ServiceProvider
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
        $container->singleton(CartService::class);
        $container->singleton(CartRepositoryInterface::class, CartRepository::class);
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

        add_action('init', array( $this, 'registerSession' ));
        $this->markBooted();
    }

    /**
     * Register cart session handling.
     *
     * Shell: real session wiring lands with the cart domain issue.
     *
     * @return void
     */
    public function registerSession(): void
    {
    }
}
