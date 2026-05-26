<?php
/**
 * Plugin activation handler.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

/**
 * Class Activator
 *
 * Handles plugin activation tasks: options, roles, schema version, rewrite rules.
 */
class Activator {

	/**
	 * Plugin version used for schema tracking.
	 *
	 * @var string
	 */
	private const SCHEMA_VERSION = '0.1.0';

	/**
	 * Activate the plugin.
	 *
	 * @return void
	 */
	public static function activate(): void {
		self::set_default_options();
		self::create_roles();
		self::set_schema_version();

		// Flush rewrite rules so CPT/taxonomy endpoints are available immediately.
		flush_rewrite_rules();
	}

	/**
	 * Set default plugin options.
	 *
	 * @return void
	 */
	private static function set_default_options(): void {
		$defaults = array(
			'smooth_restaurant_currency'             => 'USD',
			'smooth_restaurant_currency_position'    => 'before',
			'smooth_restaurant_decimal_separator'    => '.',
			'smooth_restaurant_thousand_separator'   => ',',
			'smooth_restaurant_date_format'          => 'Y-m-d',
			'smooth_restaurant_time_format'          => 'H:i',
			'smooth_restaurant_reservation_interval' => 30,
			'smooth_restaurant_order_prefix'         => 'SR-',
			'smooth_restaurant_enable_online_orders' => 'yes',
		);

		foreach ( $defaults as $option => $value ) {
			add_option( $option, $value );
		}
	}

	/**
	 * Create custom roles with appropriate capabilities.
	 *
	 * @return void
	 */
	private static function create_roles(): void {
		$admin = get_role( 'administrator' );

		// Base capabilities shared by all custom roles.
		$base_caps = array(
			'read' => true,
		);

		// Staff role: can manage menu items and reservations.
		$staff_caps = array_merge(
			$base_caps,
			array(
				'edit_smooth_restaurant_menu_items'        => true,
				'edit_others_smooth_restaurant_menu_items' => true,
				'publish_smooth_restaurant_menu_items'     => true,
				'read_smooth_restaurant_menu_item'         => true,
				'delete_smooth_restaurant_menu_items'      => true,
				'edit_smooth_restaurant_reservations'      => true,
				'edit_others_smooth_restaurant_reservations' => true,
				'publish_smooth_restaurant_reservations'   => true,
				'read_smooth_restaurant_reservation'       => true,
				'delete_smooth_restaurant_reservations'    => true,
			)
		);

		add_role(
			'smooth_restaurant_staff',
			__( 'Restaurant Staff', 'smooth-restaurant' ),
			$staff_caps
		);

		// Kitchen role: can read orders and menu items.
		$kitchen_caps = array_merge(
			$base_caps,
			array(
				'read_smooth_restaurant_menu_item'     => true,
				'read_smooth_restaurant_order'         => true,
				'edit_smooth_restaurant_orders'        => true,
				'edit_others_smooth_restaurant_orders' => true,
			)
		);

		add_role(
			'smooth_restaurant_kitchen',
			__( 'Kitchen Staff', 'smooth-restaurant' ),
			$kitchen_caps
		);

		// Manager role: full plugin management except site-level settings.
		$manager_caps = array_merge(
			$staff_caps,
			$kitchen_caps,
			array(
				'edit_smooth_restaurant_orders'        => true,
				'edit_others_smooth_restaurant_orders' => true,
				'publish_smooth_restaurant_orders'     => true,
				'read_smooth_restaurant_order'         => true,
				'delete_smooth_restaurant_orders'      => true,
				'manage_smooth_restaurant_settings'    => true,
			)
		);

		add_role(
			'smooth_restaurant_manager',
			__( 'Restaurant Manager', 'smooth-restaurant' ),
			$manager_caps
		);

		// Driver role: can read and update assigned orders.
		$driver_caps = array_merge(
			$base_caps,
			array(
				'read_smooth_restaurant_order'  => true,
				'edit_smooth_restaurant_orders' => true,
			)
		);

		add_role(
			'smooth_restaurant_driver',
			__( 'Delivery Driver', 'smooth-restaurant' ),
			$driver_caps
		);

		// Grant administrator full plugin capabilities.
		if ( $admin instanceof \WP_Role ) {
			$all_caps = array_keys( $manager_caps );
			foreach ( $all_caps as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Set the schema version option.
	 *
	 * @return void
	 */
	private static function set_schema_version(): void {
		update_option( 'smooth_restaurant_db_version', self::SCHEMA_VERSION );
	}
}
