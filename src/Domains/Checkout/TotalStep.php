<?php

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Checkout;

use SmoothRestaurant\Domains\Shared\Money;

/**
 * One composable stage of the checkout totals pipeline.
 *
 * Each step receives the running total plus the calculation context and
 * returns the new running total. Steps never mutate their inputs (Money is
 * immutable) and never touch I/O: rates, fees, and coupons arrive via
 * $context so the pipeline stays unit-testable without WordPress.
 */
interface TotalStep
{
    /**
     * Apply this stage to the running total.
     *
     * @param array<string, mixed> $context Calculation inputs (items, discount_cents, tax_rate, fee_cents).
     */
    public function apply(Money $running, array $context): Money;
}
