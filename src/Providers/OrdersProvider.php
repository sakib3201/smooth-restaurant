<?php

/**
 * Orders service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Contracts\OrderItemRepositoryInterface;
use SmoothRestaurant\Contracts\OrderRepositoryInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Database\Repositories\OrderItemRepository;
use SmoothRestaurant\Database\Repositories\OrderRepository;
use SmoothRestaurant\Domains\Orders\OrderService;

/**
 * Class OrdersProvider
 *
 * Binds order/ledger services in register() (bind-only) and hooks order
 * status handling in boot() on diner frontend requests only.
 */
final class OrdersProvider extends ServiceProvider
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
        $container->singleton(OrderService::class);
        $container->singleton(OrderRepositoryInterface::class, OrderRepository::class);
        $container->singleton(OrderItemRepositoryInterface::class, OrderItemRepository::class);
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

        add_action('init', array( $this, 'registerOrderStatuses' ));
        $this->markBooted();
    }

    /**
     * Register order statuses.
     *
     * Shell: real status-machine wiring lands with the orders domain issue.
     *
     * @return void
     */
    public function registerOrderStatuses(): void
    {
    }
}
