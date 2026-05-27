<?php
/**
 * Reservation settings manager.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Reservation;

/**
 * Class Settings
 *
 * Manages reservation-related settings with defaults.
 */
class Settings {

	/**
	 * Default settings.
	 *
	 * @var array<string, mixed>
	 */
	private const DEFAULTS = array(
		'smooth_restaurant_reservation_interval'           => 30,
		'smooth_restaurant_reservation_duration'           => 90,
		'smooth_restaurant_reservation_total_covers'       => 40,
		'smooth_restaurant_reservation_max_booking_window' => 30,
		'smooth_restaurant_reservation_min_advance'        => 2,
		'smooth_restaurant_reservation_auto_confirm'       => 'no',
		'smooth_restaurant_recaptcha_enabled'              => 'no',
		'smooth_restaurant_recaptcha_site_key'             => '',
		'smooth_restaurant_recaptcha_secret_key'           => '',
		'smooth_restaurant_recaptcha_threshold'            => 0.5,
		'smooth_restaurant_email_new_subject'              => 'Your reservation request at {restaurant_name}',
		'smooth_restaurant_email_new_body'                 => "Hi {customer_name},\n\nThank you for your reservation request at {restaurant_name} for {party_size} people on {reservation_date} at {reservation_time}.\n\nWe will confirm your booking shortly.\n\n{restaurant_name}",
		'smooth_restaurant_email_confirmed_subject'        => 'Your reservation is confirmed at {restaurant_name}',
		'smooth_restaurant_email_confirmed_body'           => "Hi {customer_name},\n\nYour reservation for {party_size} people on {reservation_date} at {reservation_time} is confirmed.\n\nWe look forward to seeing you!\n\n{restaurant_name}",
		'smooth_restaurant_email_reminder_subject'         => 'Reminder: Your reservation at {restaurant_name} today',
		'smooth_restaurant_email_reminder_body'            => "Hi {customer_name},\n\nThis is a friendly reminder of your reservation at {restaurant_name} for {party_size} people today at {reservation_time}.\n\nSee you soon!\n\n{restaurant_name}",
		'smooth_restaurant_email_cancelled_subject'        => 'Your reservation at {restaurant_name} has been cancelled',
		'smooth_restaurant_email_cancelled_body'           => "Hi {customer_name},\n\nYour reservation for {party_size} people on {reservation_date} at {reservation_time} has been cancelled.\n\nIf you have any questions, please contact us.\n\n{restaurant_name}",
	);

	/**
	 * Seed all default reservation settings on activation.
	 *
	 * @return void
	 */
	public static function seed_defaults(): void {
		foreach ( self::DEFAULTS as $option => $value ) {
			add_option( $option, $value );
		}
	}

	/**
	 * Get a reservation setting.
	 *
	 * @param string $key     Setting key (without prefix).
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public static function get( string $key, mixed $default = null ): mixed {
		$option = 'smooth_restaurant_' . $key;
		$value  = get_option( $option );

		if ( false === $value ) {
			return self::DEFAULTS[ $option ] ?? $default;
		}

		return $value;
	}

	/**
	 * Get the reservation interval in minutes.
	 *
	 * @return int
	 */
	public static function get_interval(): int {
		return (int) self::get( 'reservation_interval', 30 );
	}

	/**
	 * Get the reservation duration in minutes.
	 *
	 * @return int
	 */
	public static function get_duration(): int {
		return (int) self::get( 'reservation_duration', 90 );
	}

	/**
	 * Get total covers (restaurant capacity).
	 *
	 * @return int
	 */
	public static function get_total_covers(): int {
		return (int) self::get( 'reservation_total_covers', 40 );
	}

	/**
	 * Get max booking window in days.
	 *
	 * @return int
	 */
	public static function get_max_booking_window(): int {
		return (int) self::get( 'reservation_max_booking_window', 30 );
	}

	/**
	 * Get minimum advance booking in hours.
	 *
	 * @return int
	 */
	public static function get_min_advance(): int {
		return (int) self::get( 'reservation_min_advance', 2 );
	}

	/**
	 * Check if auto-confirm is enabled.
	 *
	 * @return bool
	 */
	public static function is_auto_confirm(): bool {
		return 'yes' === self::get( 'reservation_auto_confirm', 'no' );
	}
}
