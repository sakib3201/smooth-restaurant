<?php

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Database\MigrationRunner;

/**
 * Database platform provider.
 *
 * Binds the MigrationRunner (owner of the canonical smooth_db_version
 * option) into the container. Schema migrations run on the activation
 * path via Activator — never in the request path — so boot() stays
 * hook-free until domain tables land in follow-up issues.
 */
final class DatabaseProvider extends ServiceProvider
{
    /**
     * Bind services with no side effects.
     *
     * @param Container $container The DI container.
     */
    public function register(Container $container): void
    {
        $container->singleton(
            MigrationRunner::class,
            static fn (): MigrationRunner => new MigrationRunner(MigrationRunner::defaults())
        );
    }

    /**
     * Boot the provider after all providers are registered.
     *
     * No request-path hooks: migrations run on activation only.
     *
     * @param Container $container The DI container.
     */
    public function boot(Container $container): void
    {
        return;
    }
}
