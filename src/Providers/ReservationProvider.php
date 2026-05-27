<?php
/**
 * Reservation service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Admin\ReservationAdmin;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Frontend\ReservationShortcode;
use SmoothRestaurant\Reservation\Repository;
use SmoothRestaurant\Reservation\SlotEngine;
use SmoothRestaurant\Reservation\Validator;
use SmoothRestaurant\Reservation\EmailService;
use SmoothRestaurant\Reservation\RestController;

/**
 * Class ReservationProvider
 *
 * Registers reservation-related services and hooks.
 */
class ReservationProvider extends ServiceProvider {

	/**
	 * Register reservation services.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton( Repository::class );
		$container->singleton( SlotEngine::class );
		$container->singleton( Validator::class );
		$container->singleton( EmailService::class );
	}

	/**
	 * Boot reservation functionality.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$rest_controller = $container->make( RestController::class );
		add_action( 'rest_api_init', array( $rest_controller, 'register_routes' ) );

		$shortcode = $container->make( ReservationShortcode::class );
		$shortcode->register();

		$admin = $container->make( ReservationAdmin::class );
		$admin->register();

		add_action( 'smooth_restaurant_reservation_reminders', array( $this, 'send_reminders' ) );
	}

	/**
	 * Send reminder emails for upcoming confirmed reservations.
	 *
	 * @return void
	 */
	public function send_reminders(): void {
		$repository    = $this->container->make( Repository::class );
		$email_service = $this->container->make( EmailService::class );

		$upcoming = $repository->find_upcoming_for_reminders( 2 );

		foreach ( $upcoming as $reservation ) {
			$email_service->send_reminder( $reservation );
			$repository->update( (int) $reservation->id, array( 'reminder_sent' => 1 ) );
		}
	}
}
