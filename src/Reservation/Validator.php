<?php
/**
 * Reservation validator.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Reservation;

/**
 * Class Validator
 *
 * Validates reservation data and status transitions.
 */
class Validator {

	/**
	 * Valid status values.
	 *
	 * @var array<string>
	 */
	private const VALID_STATUSES = array( 'pending', 'confirmed', 'cancelled', 'no-show' );

	/**
	 * Valid status transitions.
	 *
	 * @var array<string, array<string>>
	 */
	private const VALID_TRANSITIONS = array(
		'pending'   => array( 'confirmed', 'cancelled' ),
		'confirmed' => array( 'cancelled', 'no-show' ),
		'cancelled' => array(),
		'no-show'   => array(),
	);

	/**
	 * The slot engine.
	 *
	 * @var SlotEngine
	 */
	private SlotEngine $slot_engine;

	/**
	 * Constructor.
	 *
	 * @param SlotEngine $slot_engine Slot engine instance.
	 */
	public function __construct( SlotEngine $slot_engine ) {
		$this->slot_engine = $slot_engine;
	}

	/**
	 * Validate reservation creation data.
	 *
	 * @param array<string, mixed> $data Reservation data.
	 * @return array<string, string> Array of error messages (empty if valid).
	 */
	public function validate_create( array $data ): array {
		$errors = array();

		if ( ! isset( $data['reservation_date'] ) || '' === $data['reservation_date'] || ! $this->is_valid_date( $data['reservation_date'] ) ) {
			$errors['reservation_date'] = __( 'Please select a valid date.', 'smooth-restaurant' );
		} elseif ( ! $this->slot_engine->is_within_booking_window( $data['reservation_date'] ) ) {
			$errors['reservation_date'] = __( 'Selected date is outside the booking window.', 'smooth-restaurant' );
		}

		if ( ! isset( $data['time'] ) || '' === $data['time'] || ! $this->is_valid_time( $data['time'] ) ) {
			$errors['time'] = __( 'Please select a valid time.', 'smooth-restaurant' );
		}

		if ( ! isset( $data['customer_name'] ) || '' === $data['customer_name'] ) {
			$errors['customer_name'] = __( 'Please enter your name.', 'smooth-restaurant' );
		}

		if ( ! isset( $data['customer_phone'] ) || '' === $data['customer_phone'] ) {
			$errors['customer_phone'] = __( 'Please enter your phone number.', 'smooth-restaurant' );
		}

		$party_size = isset( $data['party_size'] ) ? (int) $data['party_size'] : 0;
		if ( $party_size < 1 ) {
			$errors['party_size'] = __( 'Party size must be at least 1.', 'smooth-restaurant' );
		}

		if ( isset( $data['customer_email'] ) && is_string( $data['customer_email'] ) && '' !== $data['customer_email'] && ! is_email( $data['customer_email'] ) ) {
			$errors['customer_email'] = __( 'Please enter a valid email address.', 'smooth-restaurant' );
		}

		// Validate honeypot field.
		if ( isset( $data['website'] ) && is_string( $data['website'] ) && '' !== $data['website'] ) {
			$errors['honeypot'] = __( 'Invalid submission.', 'smooth-restaurant' );
		}

		return $errors;
	}

	/**
	 * Validate status transition.
	 *
	 * @param string $current_status Current status.
	 * @param string $new_status     New status.
	 * @return bool
	 */
	public function is_valid_transition( string $current_status, string $new_status ): bool {
		if ( ! in_array( $current_status, self::VALID_STATUSES, true ) || ! in_array( $new_status, self::VALID_STATUSES, true ) ) {
			return false;
		}

		if ( $current_status === $new_status ) {
			return true;
		}

		return in_array( $new_status, self::VALID_TRANSITIONS[ $current_status ], true );
	}

	/**
	 * Check if a value is a valid date (Y-m-d).
	 *
	 * @param string $date Date string.
	 * @return bool
	 */
	private function is_valid_date( string $date ): bool {
		$d = \DateTime::createFromFormat( 'Y-m-d', $date );
		return false !== $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Check if a value is a valid time (H:i).
	 *
	 * @param string $time Time string.
	 * @return bool
	 */
	private function is_valid_time( string $time ): bool {
		$t = \DateTime::createFromFormat( 'H:i', $time );
		return false !== $t && $t->format( 'H:i' ) === $time;
	}
}
