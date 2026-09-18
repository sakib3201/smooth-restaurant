<?php
/**
 * REST service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;

/**
 * Class RestProvider
 *
 * Hooks REST route registration in boot() on REST requests only.
 * Shell: route wiring lands with the REST follow-up issue.
 */
final class RestProvider extends ServiceProvider {

	/**
	 * Register services with the container.
	 *
	 * Bind-only: no hooks, no database access, no translation calls.
	 * Shell: no REST bindings yet.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function register( Container $container ): void {
	}

	/**
	 * Boot the provider after all providers are registered.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		if ( ! $this->isDoingRest() ) {
			return;
		}

		add_action( 'rest_api_init', array( $this, 'registerRoutes' ) );
		$this->markBooted();
	}

	/**
	 * Register REST routes.
	 *
	 * Shell: real route wiring lands with the REST follow-up issue.
	 *
	 * @return void
	 */
	public function registerRoutes(): void {
	}
}
