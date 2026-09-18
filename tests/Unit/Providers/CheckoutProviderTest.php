<?php

/**
 * Unit tests for the CheckoutProvider money bindings.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Domains\Checkout\TotalsCalculator;
use SmoothRestaurant\Domains\Shared\Money;
use SmoothRestaurant\Providers\CheckoutProvider;

/**
 * Class CheckoutProviderTest
 */
class CheckoutProviderTest extends TestCase
{
    /**
     * Set up clean stub state for each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        sr_test_reset_stubs();
    }

    /**
     * Tear down stub state after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        sr_test_reset_stubs();
        parent::tearDown();
    }

    /**
     * Test that register() binds the totals pipeline as singletons without hooks.
     *
     * @return void
     */
    public function test_register_binds_totals_pipeline_without_hooks(): void
    {
        $container = new Container();
        $container->register(CheckoutProvider::class);

        $this->assertFalse(has_action('template_redirect'));
        $this->assertTrue($container->has(TotalsCalculator::class));
        $this->assertTrue($container->has(Money::class));
    }

    /**
     * Test that the totals calculator resolves as a shared singleton.
     *
     * @return void
     */
    public function test_totals_calculator_resolves_as_singleton(): void
    {
        $container = new Container();
        $container->register(CheckoutProvider::class);

        $first = $container->make(TotalsCalculator::class);
        $second = $container->make(TotalsCalculator::class);

        $this->assertInstanceOf(TotalsCalculator::class, $first);
        $this->assertSame($first, $second);
    }

    /**
     * Test that the bound Money anchor is a zero amount.
     *
     * @return void
     */
    public function test_money_anchor_is_zero(): void
    {
        $container = new Container();
        $container->register(CheckoutProvider::class);

        $money = $container->make(Money::class);

        $this->assertInstanceOf(Money::class, $money);
        $this->assertTrue($money->equals(Money::zero('USD')));
    }
}
