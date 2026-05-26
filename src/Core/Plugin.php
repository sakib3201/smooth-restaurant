<?php
/**
 * Main plugin class.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

/**
 * Class Plugin
 *
 * Main plugin singleton that bootstraps all functionality.
 */
final class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Private constructor.
	 */
	private function __construct() {
		$this->container = new Container();
	}

	/**
	 * Get the plugin instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Boot the plugin.
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->registerProviders();
		$this->container->boot();
	}

	/**
	 * Register all service providers.
	 *
	 * @return void
	 */
	private function registerProviders(): void {
		$providers = array(
			\SmoothRestaurant\Providers\CoreProvider::class,
			// Future providers will be added here:
			// \SmoothRestaurant\Providers\DatabaseProvider::class,
			// \SmoothRestaurant\Providers\MenuProvider::class,
			// \SmoothRestaurant\Providers\OrderProvider::class,
			// \SmoothRestaurant\Providers\ReservationProvider::class,
			// \SmoothRestaurant\Providers\AssetProvider::class,
			// \SmoothRestaurant\Providers\RouteProvider::class,
			// \SmoothRestaurant\Providers\AdminProvider::class,
			// \SmoothRestaurant\Providers\FrontendProvider::class,
		);

		foreach ( $providers as $provider ) {
			$this->container->register( $provider );
		}
	}

	/**
	 * Get the service container.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 *
	 * @throws \Exception Always throws.
	 */
	public function __wakeup(): void {
		throw new \Exception( 'Cannot unserialize singleton' );
	}
}
