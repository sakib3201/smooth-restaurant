<?php
/**
 * Plugin deactivation handler.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

/**
 * Class Deactivator
 *
 * Handles plugin deactivation tasks. Preserves all data.
 */
class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		self::clear_cron_events();
		self::clear_transients();
	}

	/**
	 * Remove custom cron events registered by the plugin.
	 *
	 * @return void
	 */
	private static function clear_cron_events(): void {
		$hooks = array(
			'smooth_restaurant_hourly_cleanup',
			'smooth_restaurant_daily_report',
			'smooth_restaurant_process_pending_orders',
			'smooth_restaurant_reservation_reminders',
		);

		foreach ( $hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( false !== $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
		}
	}

	/**
	 * Clear plugin-specific transients.
	 *
	 * @return void
	 */
	private static function clear_transients(): void {
		$transients = array(
			'smooth_restaurant_menu_cache',
			'smooth_restaurant_order_stats',
			'smooth_restaurant_reservation_summary',
			'smooth_restaurant_daily_revenue',
		);

		foreach ( $transients as $transient ) {
			delete_transient( $transient );
		}
	}
}
