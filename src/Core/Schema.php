<?php
/**
 * Database schema manager.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

/**
 * Class Schema
 *
 * Manages database schema versioning and upgrade routines.
 */
class Schema {

	/**
	 * Target schema version.
	 *
	 * @var string
	 */
	private const TARGET_VERSION = '0.1.0';

	/**
	 * Option key for storing the current schema version.
	 *
	 * @var string
	 */
	private const VERSION_OPTION = 'smooth_restaurant_db_version';

	/**
	 * Get the current schema version from the database.
	 *
	 * @return string
	 */
	public static function get_version(): string {
		$version = get_option( self::VERSION_OPTION, '0.0.0' );

		return is_string( $version ) ? $version : '0.0.0';
	}

	/**
	 * Install the initial schema.
	 *
	 * @return void
	 */
	public static function install(): void {
		self::set_version( self::TARGET_VERSION );
	}

	/**
	 * Check the current schema version and run upgrade routines if needed.
	 *
	 * @return void
	 */
	public static function update(): void {
		$current_version = self::get_version();

		if ( version_compare( $current_version, self::TARGET_VERSION, '>=' ) ) {
			return;
		}

		// Run upgrade routines in order.
		if ( version_compare( $current_version, '0.1.0', '<' ) ) {
			self::upgrade_to_010();
		}

		// Future upgrades:
		// if ( version_compare( $current_version, '0.2.0', '<' ) ) {
		// self::upgrade_to_020();
		// }

		self::set_version( self::TARGET_VERSION );
	}

	/**
	 * Set the schema version option.
	 *
	 * @param string $version Version string.
	 * @return void
	 */
	private static function set_version( string $version ): void {
		update_option( self::VERSION_OPTION, $version );
	}

	/**
	 * Upgrade routine for version 0.1.0.
	 *
	 * @return void
	 */
	private static function upgrade_to_010(): void {
		\SmoothRestaurant\Database\Migration::create_reservations_table();
	}
}
