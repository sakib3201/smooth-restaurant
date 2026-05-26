<?php
/**
 * Abstract service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

/**
 * Class ServiceProvider
 *
 * Base class for all service providers.
 */
abstract class ServiceProvider {

	/**
	 * The container instance.
	 *
	 * @var Container
	 */
	protected Container $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container The DI container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Register services with the container.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	abstract public function register( Container $container ): void;

	/**
	 * Boot the provider after all providers are registered.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		// Override in providers that need boot-time logic.
	}
}
