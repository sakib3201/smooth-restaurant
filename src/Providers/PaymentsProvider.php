<?php

/**
 * Payments service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Domains\Payments\PaymentService;

/**
 * Class PaymentsProvider
 *
 * Binds payment services in register() (bind-only) and hooks gateway
 * registration in boot() on diner frontend requests only. Gateways
 * satisfy Contracts\GatewayInterface.
 */
final class PaymentsProvider extends ServiceProvider
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
        $container->singleton(PaymentService::class);
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

        add_action('init', array( $this, 'registerGateways' ));
        $this->markBooted();
    }

    /**
     * Register payment gateways.
     *
     * Shell: real gateway wiring lands with the payments domain issue.
     *
     * @return void
     */
    public function registerGateways(): void
    {
    }
}
