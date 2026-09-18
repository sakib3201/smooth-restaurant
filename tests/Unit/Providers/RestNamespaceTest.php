<?php

/**
 * Unit tests for the REST namespace helpers.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Providers\RestProvider;

/**
 * Class RestNamespaceTest
 */
class RestNamespaceTest extends TestCase
{
    /**
     * Test the namespace constant pins the v1 contract.
     *
     * @return void
     */
    public function test_namespace_is_versioned(): void
    {
        $this->assertSame('smooth/v1', RestProvider::NAMESPACE);
    }

    /**
     * Test route() normalizes path fragments.
     *
     * @return void
     */
    public function test_route_normalizes_fragments(): void
    {
        $this->assertSame('smooth/v1/orders', RestProvider::route('orders'));
        $this->assertSame('smooth/v1/orders', RestProvider::route('/orders'));
        $this->assertSame('smooth/v1/orders', RestProvider::route('/orders/'));
        $this->assertSame('smooth/v1', RestProvider::route(''));
        $this->assertSame('smooth/v1', RestProvider::route('/'));
    }
}
