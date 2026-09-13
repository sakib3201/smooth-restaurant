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
 * Handles plugin activation tasks. Rebuild issues add roles, options and
 * tables here as they land.
 */
class Activator {

	/**
	 * Activate the plugin.
	 *
	 * @return void
	 */
	public static function activate(): void {
		flush_rewrite_rules();
	}
}
