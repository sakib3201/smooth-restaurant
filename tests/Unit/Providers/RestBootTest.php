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
use SmoothRestaurant\Core\Context;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Providers\AdminProvider;
use SmoothRestaurant\Providers\AssetsProvider;
use SmoothRestaurant\Providers\CoreProvider;
use SmoothRestaurant\Providers\DatabaseProvider;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\RestProvider;

/**
 * Class RestBootTest
 */
class RestBootTest extends TestCase
{
    /**
     * Test that the Rest provider registers and boots on a REST request.
     *
     * Context-gated registration excludes diner and admin providers before
     * instantiation, so they are absent (not merely bailed).
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_rest_provider_boots_on_rest_request(): void
    {
        define('REST_REQUEST', true);

        Plugin::reset();
        Context::reset();
        sr_test_reset_stubs();
        Plugin::instance()->boot();

        $indexed = array();
        foreach (Plugin::instance()->container()->providers() as $provider) {
            $indexed[ $provider::class ] = $provider;
        }

        $this->assertSame(
            array(
                CoreProvider::class,
                DatabaseProvider::class,
                AssetsProvider::class,
                RestProvider::class,
            ),
            Plugin::instance()->providerClasses()
        );

        $this->assertTrue($indexed[ RestProvider::class ]->booted());
        $this->assertTrue($indexed[ CoreProvider::class ]->booted());
        $this->assertArrayNotHasKey(MenuProvider::class, $indexed);
        $this->assertArrayNotHasKey(AdminProvider::class, $indexed);
    }
}
