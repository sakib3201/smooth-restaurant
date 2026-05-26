<?php
/**
 * Unit tests for the Plugin class.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Plugin;

/**
 * Class PluginTest
 */
class PluginTest extends TestCase {

	/**
	 * Test that Plugin::instance() returns the same singleton.
	 *
	 * @return void
	 */
	public function test_instance_returns_same_singleton(): void {
		$instance1 = Plugin::instance();
		$instance2 = Plugin::instance();

		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test that Plugin::instance() returns an instance of Plugin.
	 *
	 * @return void
	 */
	public function test_instance_returns_plugin_instance(): void {
		$instance = Plugin::instance();

		$this->assertInstanceOf( Plugin::class, $instance );
	}
}
