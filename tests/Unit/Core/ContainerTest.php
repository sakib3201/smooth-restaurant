<?php
/**
 * Unit tests for the Container class.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Core\TestableService;

/**
 * Class ContainerTest
 */
class ContainerTest extends TestCase {

	/**
	 * Test that a singleton binding resolves to the identical instance.
	 *
	 * @return void
	 */
	public function test_singleton_binding_returns_identical_instance(): void {
		$container = new Container();
		$container->singleton( TestableService::class );

		$first  = $container->make( TestableService::class );
		$second = $container->make( TestableService::class );

		$this->assertSame( $first, $second );
	}

	/**
	 * Test that a singleton closure binding executes only once.
	 *
	 * @return void
	 */
	public function test_singleton_closure_binding_executes_once(): void {
		$container = new Container();
		$calls     = 0;
		$container->singleton(
			TestableService::class,
			function () use ( &$calls ): TestableService {
				++$calls;

				return new TestableService();
			}
		);

		$first  = $container->make( TestableService::class );
		$second = $container->make( TestableService::class );

		$this->assertSame( $first, $second );
		$this->assertSame( 1, $calls );
	}

	/**
	 * Test that transient bindings resolve to new instances.
	 *
	 * @return void
	 */
	public function test_transient_binding_returns_new_instances(): void {
		$container = new Container();
		$container->bind( TestableService::class );

		$first  = $container->make( TestableService::class );
		$second = $container->make( TestableService::class );

		$this->assertNotSame( $first, $second );
	}

	/**
	 * Test that has() reports bindings and instances.
	 *
	 * @return void
	 */
	public function test_has_reports_bindings_and_instances(): void {
		$container = new Container();

		$this->assertFalse( $container->has( TestableService::class ) );

		$container->bind( TestableService::class );
		$this->assertTrue( $container->has( TestableService::class ) );

		$other = new Container();
		$this->assertFalse( $other->has( TestableService::class ) );
		$other->instance( TestableService::class, new TestableService() );
		$this->assertTrue( $other->has( TestableService::class ) );
	}

	/**
	 * Test that a pre-registered instance overrides resolution.
	 *
	 * @return void
	 */
	public function test_instance_overrides_resolution_with_test_double(): void {
		$container = new Container();
		$double    = new TestableService( 'double' );

		$container->instance( TestableService::class, $double );

		$this->assertSame( $double, $container->make( TestableService::class ) );
		$this->assertSame( 'double', $container->make( TestableService::class )->getValue() );
	}

	/**
	 * Test that resolving a class with an unresolvable scalar dependency throws.
	 *
	 * @return void
	 */
	public function test_make_throws_for_unresolvable_scalar_dependency(): void {
		$container = new Container();

		$this->expectException( RuntimeException::class );
		$container->make( ContainerNeedsScalar::class );
	}

	/**
	 * Test that resolving an unknown class throws a runtime exception.
	 *
	 * @return void
	 */
	public function test_make_throws_for_unknown_class(): void {
		$container = new Container();

		$this->expectException( RuntimeException::class );
		$container->make( 'SmoothRestaurant\\DoesNotExist\\Nope' );
	}

	/**
	 * Test that registering a provider does not pollute the instance store.
	 *
	 * @return void
	 */
	public function test_register_stores_provider_separately_from_instances(): void {
		$container = new Container();
		$container->register( ContainerFixtureProvider::class );

		$this->assertFalse( $container->has( ContainerFixtureProvider::class ) );
		$this->assertContains( ContainerFixtureProvider::class, $container->providerClasses() );
	}

	/**
	 * Test that auto-wiring resolves class dependencies as a boot-time fallback.
	 *
	 * @return void
	 */
	public function test_make_autowires_class_dependencies(): void {
		$container = new Container();

		$instance = $container->make( ContainerNeedsService::class );

		$this->assertInstanceOf( ContainerNeedsService::class, $instance );
		$this->assertInstanceOf( TestableService::class, $instance->service );
	}

	/**
	 * Test that scalar constructor defaults are honored by auto-wiring.
	 *
	 * @return void
	 */
	public function test_make_uses_scalar_default_when_available(): void {
		$container = new Container();

		$instance = $container->make( ContainerHasScalarDefault::class );

		$this->assertSame( 'fallback', $instance->name );
	}
}

/**
 * Fixture: service with an unresolvable required scalar dependency.
 */
class ContainerNeedsScalar {

	/**
	 * Constructor.
	 *
	 * @param string $name Required scalar with no default.
	 */
	public function __construct( public string $name ) {}
}

/**
 * Fixture: service with a scalar default.
 */
class ContainerHasScalarDefault {

	/**
	 * Constructor.
	 *
	 * @param string $name Scalar with a default value.
	 */
	public function __construct( public string $name = 'fallback' ) {}
}

/**
 * Fixture: service depending on another service.
 */
class ContainerNeedsService {

	/**
	 * Constructor.
	 *
	 * @param TestableService $service Injected service.
	 */
	public function __construct( public TestableService $service ) {}
}

/**
 * Fixture: minimal provider for store-separation assertions.
 */
class ContainerFixtureProvider extends ServiceProvider {

	/**
	 * Register services with the container.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton( TestableService::class );
	}
}
