<?php
/**
 * Reservation repository.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Reservation;

/**
 * Class Repository
 *
 * Handles CRUD operations for the sr_reservations table.
 */
class Repository {

	/**
	 * The database table name (with prefix).
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'sr_reservations';
	}

	/**
	 * Create a new reservation.
	 *
	 * @param array<string, mixed> $data Reservation data.
	 * @return int|false The new reservation ID or false on failure.
	 */
	public function create( array $data ): int|false {
		global $wpdb;

		$defaults = array(
			'status'        => 'pending',
			'reminder_sent' => 0,
		);

		$data = wp_parse_args( $data, $defaults );

		$inserted = $wpdb->insert(
			$this->table,
			array(
				'reservation_date' => $data['reservation_date'],
				'time'             => $data['time'],
				'party_size'       => (int) $data['party_size'],
				'customer_name'    => sanitize_text_field( $data['customer_name'] ),
				'customer_phone'   => sanitize_text_field( $data['customer_phone'] ),
				'customer_email'   => isset( $data['customer_email'] ) ? sanitize_email( $data['customer_email'] ) : null,
				'special_requests' => isset( $data['special_requests'] ) ? sanitize_textarea_field( $data['special_requests'] ) : null,
				'status'           => sanitize_text_field( $data['status'] ),
				'reminder_sent'    => (int) $data['reminder_sent'],
			),
			array(
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
			)
		);

		if ( false === $inserted ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Find a reservation by ID.
	 *
	 * @param int $id Reservation ID.
	 * @return object|null Reservation object or null.
	 */
	public function find( int $id ): ?object {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id )
		);

		return $row ?: null;
	}

	/**
	 * Update a reservation.
	 *
	 * @param int                  $id   Reservation ID.
	 * @param array<string, mixed> $data Data to update.
	 * @return bool True on success.
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;

		$allowed = array_intersect_key(
			$data,
			array_flip(
				array(
					'reservation_date',
					'time',
					'party_size',
					'customer_name',
					'customer_phone',
					'customer_email',
					'special_requests',
					'status',
					'reminder_sent',
				)
			)
		);

		if ( empty( $allowed ) ) {
			return false;
		}

		$result = $wpdb->update( $this->table, $allowed, array( 'id' => $id ) );

		return false !== $result;
	}

	/**
	 * Delete a reservation.
	 *
	 * @param int $id Reservation ID.
	 * @return bool True on success.
	 */
	public function delete( int $id ): bool {
		global $wpdb;

		$result = $wpdb->delete( $this->table, array( 'id' => $id ), array( '%d' ) );

		return false !== $result && $result > 0;
	}

	/**
	 * Find reservations by date range.
	 *
	 * @param string               $start_date Start date (Y-m-d).
	 * @param string               $end_date   End date (Y-m-d).
	 * @param array<string, mixed> $args Optional args (status, orderby, order).
	 * @return array<object>
	 */
	public function find_by_date_range( string $start_date, string $end_date, array $args = array() ): array {
		global $wpdb;

		$status   = $args['status'] ?? '';
		$orderby  = $args['orderby'] ?? 'reservation_date';
		$order    = strtoupper( $args['order'] ?? 'ASC' );
		$per_page = (int) ( $args['per_page'] ?? 20 );
		$paged    = (int) ( $args['paged'] ?? 1 );
		$offset   = ( $paged - 1 ) * $per_page;

		$where  = 'WHERE reservation_date BETWEEN %s AND %s';
		$params = array( $start_date, $end_date );

		if ( $status && is_string( $status ) ) {
			$where   .= ' AND status = %s';
			$params[] = $status;
		}

		$orderby = sanitize_sql_orderby( "{$orderby} {$order}" ) ?: 'reservation_date ASC';

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table} {$where} ORDER BY {$orderby} LIMIT %d OFFSET %d",
			array_merge( $params, array( $per_page, $offset ) )
		);

		return $wpdb->get_results( $sql ) ?: array();
	}

	/**
	 * Find overlapping reservations for a given date and time range.
	 *
	 * @param string $date      Reservation date (Y-m-d).
	 * @param string $start_time Start time (H:i:s).
	 * @param string $end_time   End time (H:i:s).
	 * @return array<object>
	 */
	public function find_overlapping( string $date, string $start_time, string $end_time ): array {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table}
			WHERE reservation_date = %s
			AND status IN ('pending', 'confirmed')
			AND (
				(time <= %s AND ADDTIME(time, SEC_TO_TIME(%d)) > %s)
				OR (time >= %s AND time < %s)
			)
			ORDER BY time ASC",
			$date,
			$start_time,
			Settings::get_duration() * 60,
			$start_time,
			$start_time,
			$end_time
		);

		return $wpdb->get_results( $sql ) ?: array();
	}

	/**
	 * Count reservations by status.
	 *
	 * @param string $date   Date (Y-m-d).
	 * @param string $status Status filter.
	 * @return int
	 */
	public function count_by_date_status( string $date, string $status = '' ): int {
		global $wpdb;

		$where  = 'WHERE reservation_date = %s';
		$params = array( $date );

		if ( $status ) {
			$where   .= ' AND status = %s';
			$params[] = $status;
		}

		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} {$where}", ...$params )
		);
	}

	/**
	 * Count reservations by date range and optional status.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param string $status     Status filter.
	 * @return int
	 */
	public function count_by_date_range( string $start_date, string $end_date, string $status = '' ): int {
		global $wpdb;

		$where  = 'WHERE reservation_date BETWEEN %s AND %s';
		$params = array( $start_date, $end_date );

		if ( $status ) {
			$where   .= ' AND status = %s';
			$params[] = $status;
		}

		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} {$where}", ...$params )
		);
	}

	/**
	 * Get upcoming confirmed reservations needing reminders.
	 *
	 * @param int $hours_ahead Hours ahead to look.
	 * @return array<object>
	 */
	public function find_upcoming_for_reminders( int $hours_ahead = 2 ): array {
		global $wpdb;

		$now    = current_time( 'mysql' );
		$future = gmdate( 'Y-m-d H:i:s', strtotime( "+{$hours_ahead} hours", strtotime( $now ) ) );

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table}
			WHERE status = 'confirmed'
			AND reminder_sent = 0
			AND CONCAT(reservation_date, ' ', time) BETWEEN %s AND %s
			ORDER BY reservation_date ASC, time ASC",
			$now,
			$future
		);

		return $wpdb->get_results( $sql ) ?: array();
	}
}
