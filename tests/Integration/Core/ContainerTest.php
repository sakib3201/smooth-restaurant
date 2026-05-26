<?php
/**
 * Integration tests for the Container class.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Integration\Core;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\TestableService;

/**
 * Class ContainerTest
 */
class ContainerTest extends TestCase {

	/**
	 * Test that the container can bind and resolve a simple class.
	 *
	 * @return void
	 */
	public function test_container_can_bind_and_resolve_simple_class(): void {
		$container = new Container();

		$container->bind( TestableService::class );
		$instance = $container->make( TestableService::class );

		$this->assertInstanceOf( TestableService::class, $instance );
		$this->assertSame( 'default', $instance->getValue() );
	}
}
