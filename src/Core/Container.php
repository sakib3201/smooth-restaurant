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
use RuntimeException;

/**
 * Class Container
 *
 * Lightweight DI container inspired by Laravel's service container.
 *
 * Two stores are kept strictly separate:
 *
 * - `$instances` holds keyed singleton instances (including test doubles
 *   pre-registered via `instance()`).
 * - `$providers` holds the ordered list of registered service providers.
 *
 * Explicit closure/singleton bindings resolve without reflection on hot
 * paths (singletons are memoized after first resolution). Class auto-wiring
 * via reflection remains available as a boot-time fallback only.
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
	 * Resolved singleton instances, keyed by abstract.
	 *
	 * @var array<string, object>
	 */
	private array $instances = array();

	/**
	 * Registered service providers, in registration order.
	 *
	 * @var array<int, ServiceProvider>
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
	 * Check whether an abstract has a binding or a stored instance.
	 *
	 * @param string $abstract The abstract class or interface.
	 * @return bool
	 */
	public function has( string $abstract ): bool {
		return isset( $this->bindings[ $abstract ] ) || isset( $this->instances[ $abstract ] );
	}

	/**
	 * Pre-register an existing object as the singleton for an abstract.
	 *
	 * Used to inject test doubles: subsequent `make()` calls for the
	 * abstract return this exact object.
	 *
	 * @param string $abstract The abstract class or interface.
	 * @param object $instance The instance to return.
	 * @return void
	 */
	public function instance( string $abstract, object $instance ): void {
		$this->instances[ $abstract ]  = $instance;
		$this->singletons[ $abstract ] = true;
	}

	/**
	 * Resolve an instance from the container.
	 *
	 * @param string $abstract The abstract class or interface.
	 * @return object
	 * @throws RuntimeException If the abstract cannot be resolved.
	 */
	public function make( string $abstract ): object {
		if ( isset( $this->instances[ $abstract ] ) ) {
			return $this->instances[ $abstract ];
		}

		$concrete = $this->bindings[ $abstract ] ?? $abstract;

		if ( $concrete instanceof Closure ) {
			$instance = $concrete( $this );
		} else {
			$instance = $this->resolve( $concrete );
		}

		if ( isset( $this->singletons[ $abstract ] ) ) {
			$this->instances[ $abstract ] = $instance;
		}

		return $instance;
	}

	/**
	 * Resolve a class instance with automatic dependency injection.
	 *
	 * Auto-wiring is a boot-time fallback: prefer explicit
	 * closure/singleton bindings for services on hot paths.
	 *
	 * @param string $class The class name.
	 * @return object
	 * @throws RuntimeException If the class does not exist.
	 */
	private function resolve( string $class ): object {
		if ( ! class_exists( $class ) ) {
			throw new RuntimeException( "Cannot resolve [{$class}]: class does not exist." );
		}

		$reflector   = new ReflectionClass( $class );
		$constructor = $reflector->getConstructor();

		if ( $constructor === null ) {
			return new $class();
		}

		$dependencies = array_map(
			fn ( ReflectionParameter $param ): mixed => $this->resolveDependency( $param, $class ),
			$constructor->getParameters()
		);

		return $reflector->newInstanceArgs( $dependencies );
	}

	/**
	 * Resolve a single dependency.
	 *
	 * @param ReflectionParameter $param  The parameter to resolve.
	 * @param string              $class  The class being resolved, for error context.
	 * @return mixed
	 * @throws RuntimeException If the dependency cannot be resolved.
	 */
	private function resolveDependency( ReflectionParameter $param, string $class ): mixed {
		$type = $param->getType();

		if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
			return $this->make( $type->getName() );
		}

		if ( $param->isDefaultValueAvailable() ) {
			return $param->getDefaultValue();
		}

		throw new RuntimeException( "Cannot resolve dependency [{$param->getName()}] for class [{$class}]." );
	}

	/**
	 * Register a service provider.
	 *
	 * The container itself is bound first so auto-wiring injects this exact
	 * container into the provider constructor. Registering the same provider
	 * class twice is a no-op, which keeps repeated `Plugin::boot()` calls safe.
	 *
	 * @param string $providerClass The provider class name.
	 * @return void
	 */
	public function register( string $providerClass ): void {
		foreach ( $this->providers as $registered ) {
			if ( $registered::class === $providerClass ) {
				return;
			}
		}

		$this->instance( self::class, $this );

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
			if ( ! in_array( $provider, $this->bootedProviders, true ) ) {
				$provider->boot( $this );
				$this->bootedProviders[] = $provider;
			}
		}
	}

	/**
	 * Get the registered provider instances, in registration order.
	 *
	 * @return array<int, ServiceProvider>
	 */
	public function providers(): array {
		return $this->providers;
	}

	/**
	 * Get the class names of registered providers, in registration order.
	 *
	 * @return array<int, class-string<ServiceProvider>>
	 */
	public function providerClasses(): array {
		return array_map(
			static fn ( ServiceProvider $provider ): string => $provider::class,
			$this->providers
		);
	}
}
