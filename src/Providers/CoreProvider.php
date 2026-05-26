<?php
/**
 * Core service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Admin\AdminMenu;
use SmoothRestaurant\Admin\Assets;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Frontend\Shortcode;

/**
 * Class CoreProvider
 *
 * Registers core plugin services.
 */
class CoreProvider extends ServiceProvider {

	/**
	 * Register core services.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function register( Container $container ): void {
		// Core services will be registered here.
	}

	/**
	 * Boot core functionality.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$admin_menu = new AdminMenu();
		$admin_menu->register();

		$admin_assets = new Assets();
		$admin_assets->register();

		$shortcode = new Shortcode();
		$shortcode->register();
	}
}
