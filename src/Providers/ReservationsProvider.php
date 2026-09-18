<?php
/**
 * Reservations service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Domains\Reservations\ReservationService;

/**
 * Class ReservationsProvider
 *
 * Binds reservation services in register() (bind-only) and hooks
 * booking handling in boot() on diner frontend requests only.
 */
final class ReservationsProvider extends ServiceProvider {

	/**
	 * Register services with the container.
	 *
	 * Bind-only: no hooks, no database access, no translation calls.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton( ReservationService::class );
	}

	/**
	 * Boot the provider after all providers are registered.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		if ( $this->isBackendRequest() ) {
			return;
		}

		add_action( 'init', array( $this, 'registerBookingHandler' ) );
		$this->markBooted();
	}

	/**
	 * Register booking request handling.
	 *
	 * Shell: real booking wiring lands with the reservations domain issue.
	 *
	 * @return void
	 */
	public function registerBookingHandler(): void {
	}
}
