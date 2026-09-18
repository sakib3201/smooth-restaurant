<?php
/**
 * REST-context boot test.
 *
 * Runs in a separate process because REST_REQUEST is a process-global
 * constant: once defined it cannot be undefined for other tests.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Providers\AdminProvider;
use SmoothRestaurant\Providers\CoreProvider;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\RestProvider;

/**
 * Class RestBootTest
 */
class RestBootTest extends TestCase {

	/**
	 * Test that the Rest provider boots on a REST request.
	 *
	 * @return void
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState( false )]
	public function test_rest_provider_boots_on_rest_request(): void {
		define( 'REST_REQUEST', true );

		Plugin::reset();
		sr_test_reset_stubs();
		Plugin::instance()->boot();

		$indexed = array();
		foreach ( Plugin::instance()->container()->providers() as $provider ) {
			$indexed[ $provider::class ] = $provider;
		}

		$this->assertTrue( $indexed[ RestProvider::class ]->booted() );
		$this->assertTrue( $indexed[ CoreProvider::class ]->booted() );
		$this->assertFalse( $indexed[ MenuProvider::class ]->booted() );
		$this->assertFalse( $indexed[ AdminProvider::class ]->booted() );
	}
}
