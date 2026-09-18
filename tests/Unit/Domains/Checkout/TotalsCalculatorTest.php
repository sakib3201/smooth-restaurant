<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Domains\Checkout;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Domains\Checkout\DiscountStep;
use SmoothRestaurant\Domains\Checkout\FeeStep;
use SmoothRestaurant\Domains\Checkout\LineItemStep;
use SmoothRestaurant\Domains\Checkout\TaxStep;
use SmoothRestaurant\Domains\Checkout\TotalsCalculator;
use SmoothRestaurant\Domains\Shared\Money;

/**
 * Unit tests for the composable checkout totals pipeline.
 *
 * Covers default step ordering (line, discount, tax, fee), the
 * coupon-exceeds-subtotal floor at zero, and the
 * smooth_restaurant_checkout_total_steps filter seam.
 */
final class TotalsCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        sr_test_reset_stubs();
    }

    protected function tearDown(): void
    {
        remove_filter('smooth_restaurant_checkout_total_steps');
        sr_test_reset_stubs();
        parent::tearDown();
    }

    public function test_default_steps_run_line_discount_tax_fee(): void
    {
        $calculator = new TotalsCalculator();

        $this->assertSame(
            [LineItemStep::class, DiscountStep::class, TaxStep::class, FeeStep::class],
            array_map(static fn ($step): string => $step::class, $calculator->steps())
        );
    }

    public function test_calculate_applies_default_order(): void
    {
        $calculator = new TotalsCalculator();
        $context = [
            'items'          => [
                ['unit_cents' => 1000, 'qty' => 1],
            ],
            'discount_cents' => 100,
            'tax_rate'       => 0.10,
            'fee_cents'      => 50,
        ];

        // Line 1000, discount -> 900, tax 10% -> 990, fee -> 1040.
        $total = $calculator->calculate(Money::zero('USD'), $context);

        $this->assertTrue($total->equals(Money::fromCents(1040, 'USD')));
    }

    public function test_ordering_matters_tax_applies_after_discount(): void
    {
        $default = (new TotalsCalculator())->calculate(
            Money::zero('USD'),
            ['items' => [['unit_cents' => 1000, 'qty' => 1]], 'discount_cents' => 100, 'tax_rate' => 0.10]
        );
        $taxFirstSteps = [new LineItemStep(), new TaxStep(), new DiscountStep(), new FeeStep()];
        $taxFirst = (new TotalsCalculator($taxFirstSteps))->calculate(
            Money::zero('USD'),
            ['items' => [['unit_cents' => 1000, 'qty' => 1]], 'discount_cents' => 100, 'tax_rate' => 0.10]
        );

        // Default: (1000 - 100) * 1.10 = 990. Tax-first: 1000 * 1.10 - 100 = 1000.
        $this->assertTrue($default->equals(Money::fromCents(990, 'USD')));
        $this->assertTrue($taxFirst->equals(Money::fromCents(1000, 'USD')));
    }

    public function test_coupon_exceeding_subtotal_floors_at_zero(): void
    {
        $calculator = new TotalsCalculator();
        $context = [
            'items'          => [
                ['unit_cents' => 500, 'qty' => 1],
            ],
            'discount_cents' => 800,
            'tax_rate'       => 0.10,
            'fee_cents'      => 0,
        ];

        $total = $calculator->calculate(Money::zero('USD'), $context);

        $this->assertTrue($total->equals(Money::zero('USD')));
    }

    public function test_line_items_sum_quantities(): void
    {
        $calculator = new TotalsCalculator([new LineItemStep()]);

        $total = $calculator->calculate(
            Money::zero('USD'),
            [
                'items' => [
                    ['unit_cents' => 250, 'qty' => 2],
                    ['unit_cents' => 100, 'quantity' => 3],
                ],
            ]
        );

        $this->assertTrue($total->equals(Money::fromCents(800, 'USD')));
    }

    public function test_empty_context_returns_starting_total(): void
    {
        $calculator = new TotalsCalculator();

        $total = $calculator->calculate(Money::fromCents(700, 'USD'), []);

        $this->assertTrue($total->equals(Money::fromCents(700, 'USD')));
    }

    public function test_steps_are_filterable(): void
    {
        add_filter(
            'smooth_restaurant_checkout_total_steps',
            static fn (array $steps): array => [new FeeStep()]
        );
        $calculator = new TotalsCalculator();

        $total = $calculator->calculate(
            Money::zero('USD'),
            ['items' => [['unit_cents' => 1000, 'qty' => 1]], 'fee_cents' => 25]
        );

        // Only the fee step ran: line items were skipped by the filter.
        $this->assertTrue($total->equals(Money::fromCents(25, 'USD')));
    }

    public function test_filter_ignores_non_step_entries(): void
    {
        add_filter(
            'smooth_restaurant_checkout_total_steps',
            static fn (array $steps): array => ['not-a-step', new FeeStep()]
        );
        $calculator = new TotalsCalculator();

        $total = $calculator->calculate(Money::zero('USD'), ['fee_cents' => 25]);

        $this->assertTrue($total->equals(Money::fromCents(25, 'USD')));
    }

    public function test_constructor_rejects_non_step_entries(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TotalsCalculator($this->nonStepList());
    }

    /**
     * Intentionally invalid step list, untyped so the guard is exercised.
     *
     * @return list<mixed>
     */
    private function nonStepList(): array
    {
        return ['not-a-step'];
    }
}
