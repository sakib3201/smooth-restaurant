<?php
/**
 * Dependency injection container.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

use ReflectionClass;
use ReflectionParameter;
use Closure;

/**
 * Class Container
 *
 * Lightweight DI container inspired by Laravel's service container.
 */
final class Container {

	/**
	 * Registered bindings.
	 *
	 * @var array<string, string|Closure>
	 */
	private array $bindings = array();

	/**
	 * Singleton flags.
	 *
	 * @var array<string, bool>
	 */
	private array $singletons = array();

	/**
	 * Resolved instances.
	 *
	 * @var array<int|string, object>
	 */
	private array $providers = array();

	/**
	 * Booted providers.
	 *
	 * @var array<int, ServiceProvider>
	 */
	private array $bootedProviders = array();

	/**
	 * Bind an abstract to a concrete implementation.
	 *
	 * @param string              $abstract The abstract class or interface.
	 * @param string|Closure|null $concrete The concrete implementation.
	 * @return void
	 */
	public function bind( string $abstract, string|Closure|null $concrete = null ): void {
		$this->bindings[ $abstract ] = $concrete ?? $abstract;
	}

	/**
	 * Register a singleton binding.
	 *
	 * @param string              $abstract The abstract class or interface.
	 * @param string|Closure|null $concrete The concrete implementation.
	 * @return void
	 */
	public function singleton( string $abstract, string|Closure|null $concrete = null ): void {
		$concrete                    ??= $abstract;
		$this->bindings[ $abstract ]   = $concrete;
		$this->singletons[ $abstract ] = true;
	}

	/**
	 * Resolve an instance from the container.
	 *
	 * @param string $abstract The abstract class or interface.
	 * @return object
	 */
	public function make( string $abstract ): object {
		if ( isset( $this->singletons[ $abstract ] ) && isset( $this->providers[ $abstract ] ) ) {
			return $this->providers[ $abstract ];
		}

		$concrete = $this->bindings[ $abstract ] ?? $abstract;

		if ( $concrete instanceof Closure ) {
			$instance = $concrete( $this );
		} else {
			$instance = $this->resolve( $concrete );
		}

		if ( isset( $this->singletons[ $abstract ] ) ) {
			$this->providers[ $abstract ] = $instance;
		}

		return $instance;
	}

	/**
	 * Resolve a class instance with automatic dependency injection.
	 *
	 * @param string $class The class name.
	 * @return object
	 */
	private function resolve( string $class ): object {
		$reflector   = new ReflectionClass( $class );
		$constructor = $reflector->getConstructor();

		if ( $constructor === null ) {
			return new $class();
		}

		$dependencies = array_map(
			fn ( ReflectionParameter $param ): mixed => $this->resolveDependency( $param ),
			$constructor->getParameters()
		);

		return $reflector->newInstanceArgs( $dependencies );
	}

	/**
	 * Resolve a single dependency.
	 *
	 * @param ReflectionParameter $param The parameter to resolve.
	 * @return mixed
	 * @throws \RuntimeException If the dependency cannot be resolved.
	 */
	private function resolveDependency( ReflectionParameter $param ): mixed {
		$type = $param->getType();

		if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
			return $this->make( $type->getName() );
		}

		if ( $param->isDefaultValueAvailable() ) {
			return $param->getDefaultValue();
		}

		throw new \RuntimeException( "Cannot resolve dependency: {$param->getName()}" );
	}

	/**
	 * Register a service provider.
	 *
	 * @param string $providerClass The provider class name.
	 * @return void
	 */
	public function register( string $providerClass ): void {
		$provider = $this->make( $providerClass );

		if ( $provider instanceof ServiceProvider ) {
			$provider->register( $this );
			$this->providers[] = $provider;
		}
	}

	/**
	 * Boot all registered providers.
	 *
	 * @return void
	 */
	public function boot(): void {
		foreach ( $this->providers as $provider ) {
			if ( $provider instanceof ServiceProvider && ! in_array( $provider, $this->bootedProviders, true ) ) {
				$provider->boot( $this );
				$this->bootedProviders[] = $provider;
			}
		}
	}
}
