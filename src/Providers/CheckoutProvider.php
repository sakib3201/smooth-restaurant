<?php

/**
 * Checkout service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Contracts\CouponRepositoryInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Database\Repositories\CouponRepository;
use SmoothRestaurant\Domains\Checkout\CheckoutService;
use SmoothRestaurant\Domains\Checkout\TotalsCalculator;
use SmoothRestaurant\Domains\Shared\Money;

/**
 * Class CheckoutProvider
 *
 * Binds checkout/totals services in register() (bind-only) and hooks
 * checkout request handling in boot() on diner frontend requests only.
 */
final class CheckoutProvider extends ServiceProvider
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
        $container->singleton(CheckoutService::class);
        $container->singleton(CouponRepositoryInterface::class, CouponRepository::class);
        $container->singleton(TotalsCalculator::class);
        $container->singleton(Money::class, static fn (): Money => Money::zero('USD'));
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

        add_action('template_redirect', array( $this, 'handleCheckoutRequest' ));
        $this->markBooted();
    }

    /**
     * Handle checkout POST requests.
     *
     * Shell: real totals/validation wiring lands with the checkout
     * domain issue.
     *
     * @return void
     */
    public function handleCheckoutRequest(): void
    {
    }
}
