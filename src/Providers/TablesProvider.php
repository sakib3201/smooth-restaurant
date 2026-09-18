<?php

/**
 * Tables service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Contracts\RestaurantTableRepositoryInterface;
use SmoothRestaurant\Contracts\TableSessionRepositoryInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Database\Repositories\RestaurantTableRepository;
use SmoothRestaurant\Database\Repositories\TableSessionRepository;
use SmoothRestaurant\Domains\Tables\TableService;

/**
 * Class TablesProvider
 *
 * Binds table services in register() (bind-only) and hooks table
 * handling in boot() on diner frontend requests only.
 */
final class TablesProvider extends ServiceProvider
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
        $container->singleton(TableService::class);
        $container->singleton(RestaurantTableRepositoryInterface::class, RestaurantTableRepository::class);
        $container->singleton(TableSessionRepositoryInterface::class, TableSessionRepository::class);
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

        add_action('init', array( $this, 'registerTableHandler' ));
        $this->markBooted();
    }

    /**
     * Register table request handling.
     *
     * Shell: real table wiring lands with the tables domain issue.
     *
     * @return void
     */
    public function registerTableHandler(): void
    {
    }
}
