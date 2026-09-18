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
 * path via Activator — never in the request path. Boot only drains the
 * deferred multisite cursor: batched network migrations stash leftover
 * site IDs, and admin_init resumes them.
 */
final class DatabaseProvider extends ServiceProvider
{
    /**
     * Request contexts this provider participates in.
     *
     * The migration binding is needed on every request, so it keeps the
     * 'all' default explicitly (boot itself stays hook-free).
     *
     * @return list<string>
     */
    public static function contexts(): array
    {
        return array( 'all' );
    }

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
     * Admin context only: drains the deferred multisite-migration cursor
     * left by batched network activations.
     *
     * @param Container $container The DI container.
     */
    public function boot(Container $container): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        add_action('admin_init', array( $this, 'resumePendingMigrations' ));
        $this->markBooted();
    }

    /**
     * Resume a deferred batched network migration, if one is pending.
     *
     * No-op when the cursor is empty.
     */
    public function resumePendingMigrations(): void
    {
        $runner = $this->container->make(MigrationRunner::class);
        if ($runner instanceof MigrationRunner) {
            $runner->resumePending();
        }
    }
}
