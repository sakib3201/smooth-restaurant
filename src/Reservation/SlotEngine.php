<?php
/**
 * Reservation slot engine.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Reservation;

/**
 * Class SlotEngine
 *
 * Generates available reservation time slots based on business hours,
 * reservation settings, and existing bookings.
 */
class SlotEngine {

	/**
	 * The reservation repository.
	 *
	 * @var Repository
	 */
	private Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param Repository $repository Reservation repository.
	 */
	public function __construct( Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Get available time slots for a given date.
	 *
	 * @param string $date       Date in Y-m-d format.
	 * @param int    $party_size Party size (default 1).
	 * @return array<array<string, mixed>> Array of slots with 'time' and 'remaining_capacity'.
	 */
	public function get_available_slots( string $date, int $party_size = 1 ): array {
		$cache_key = 'sr_slots_' . md5( $date . '_' . $party_size );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$slots = $this->generate_slots( $date );

		if ( empty( $slots ) ) {
			set_transient( $cache_key, array(), 300 );
			return array();
		}

		$available = array();
		foreach ( $slots as $slot ) {
			$remaining = $this->calculate_remaining_capacity( $date, $slot['time'] );

			if ( $remaining >= $party_size && $remaining > 0 ) {
				$available[] = array(
					'time'               => $slot['time'],
					'remaining_capacity' => $remaining,
				);
			}
		}

		set_transient( $cache_key, $available, 300 );

		return $available;
	}

	/**
	 * Generate raw time slots for a date based on business hours.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return array<array<string, string>>
	 */
	private function generate_slots( string $date ): array {
		$day_of_week = (int) gmdate( 'w', strtotime( $date ) );
		$hours       = $this->get_business_hours_for_day( $day_of_week );

		if ( empty( $hours ) ) {
			return array();
		}

		$interval = Settings::get_interval();
		$slots    = array();

		foreach ( $hours as $shift ) {
			$start = strtotime( $shift['open'] );
			$end   = strtotime( $shift['close'] );

			if ( false === $start || false === $end ) {
				continue;
			}

			$current = $start;
			while ( $current < $end ) {
				$slot_time = gmdate( 'H:i', $current );

				if ( $this->is_slot_within_advance_window( $date, $slot_time ) ) {
					$slots[] = array( 'time' => $slot_time );
				}

				$current += $interval * 60;
			}
		}

		return $slots;
	}

	/**
	 * Calculate remaining capacity for a given slot.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @param string $time Time in H:i format.
	 * @return int Remaining covers.
	 */
	private function calculate_remaining_capacity( string $date, string $time ): int {
		$total_covers = Settings::get_total_covers();
		$duration     = Settings::get_duration();

		$slot_start = $time . ':00';
		$slot_end   = gmdate( 'H:i:s', strtotime( "+{$duration} minutes", strtotime( $slot_start ) ) );

		$overlapping = $this->repository->find_overlapping( $date, $slot_start, $slot_end );

		$booked = array_reduce(
			$overlapping,
			fn ( int $carry, object $res ): int => $carry + (int) $res->party_size,
			0
		);

		return max( 0, $total_covers - $booked );
	}

	/**
	 * Check if a slot is within the minimum advance booking window.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @param string $time Time in H:i format.
	 * @return bool
	 */
	private function is_slot_within_advance_window( string $date, string $time ): bool {
		$min_advance = Settings::get_min_advance();
		$slot_time   = strtotime( "{$date} {$time}:00" );
		$cutoff      = strtotime( "+{$min_advance} hours", current_time( 'timestamp' ) );

		return false !== $slot_time && $slot_time >= $cutoff;
	}

	/**
	 * Get business hours for a specific day of week.
	 *
	 * @param int $day Day of week (0=Sunday, 6=Saturday).
	 * @return array<array<string, string>> Array of shifts with 'open' and 'close'.
	 */
	private function get_business_hours_for_day( int $day ): array {
		$hours = get_option( 'smooth_restaurant_business_hours', array() );

		if ( ! is_array( $hours ) || empty( $hours[ $day ] ) ) {
			// Default fallback hours for testing/demo purposes.
			return array(
				array(
					'open'  => '11:00',
					'close' => '22:00',
				),
			);
		}

		$day_hours = $hours[ $day ];

		if ( isset( $day_hours['closed'] ) && true === $day_hours['closed'] ) {
			return array();
		}

		$shifts = array();
		foreach ( $day_hours as $shift ) {
			if ( is_array( $shift ) && isset( $shift['open'], $shift['close'] ) ) {
				$shifts[] = array(
					'open'  => sanitize_text_field( $shift['open'] ),
					'close' => sanitize_text_field( $shift['close'] ),
				);
			}
		}

		return $shifts;
	}

	/**
	 * Check if a date is within the maximum booking window.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return bool
	 */
	public function is_within_booking_window( string $date ): bool {
		$max_window = Settings::get_max_booking_window();
		$max_date   = gmdate( 'Y-m-d', strtotime( "+{$max_window} days" ) );

		return $date >= gmdate( 'Y-m-d' ) && $date <= $max_date;
	}

	/**
	 * Clear the slot cache for a date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return void
	 */
	public function clear_slot_cache( string $date ): void {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_sr_slots_' ) . '%'
			)
		);
	}
}
