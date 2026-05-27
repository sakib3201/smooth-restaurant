<?php
/**
 * Database migration for reservations table.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Database;

/**
 * Class Migration
 *
 * Handles creation and updates of custom database tables.
 */
class Migration {

	/**
	 * Create the sr_reservations table.
	 *
	 * @return void
	 */
	public static function create_reservations_table(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'sr_reservations';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			reservation_date date NOT NULL,
			time time NOT NULL,
			party_size int(11) unsigned NOT NULL DEFAULT 1,
			customer_name varchar(100) NOT NULL,
			customer_phone varchar(30) NOT NULL,
			customer_email varchar(100) DEFAULT NULL,
			special_requests text DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			reminder_sent tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY reservation_date (reservation_date),
			KEY time (time),
			KEY status (status),
			KEY party_size (party_size),
			KEY date_status (reservation_date, status)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
