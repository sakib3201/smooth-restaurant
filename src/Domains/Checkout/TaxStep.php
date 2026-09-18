<?php

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Checkout;

use SmoothRestaurant\Domains\Shared\Money;

/**
 * Applies a percentage tax rate to the running total (half-up to cents).
 *
 * Reads $context['tax_rate'] as a fraction (e.g. 0.075 for 7.5%). Missing,
 * non-numeric, or non-positive rates are a no-op; a non-finite rate throws
 * via Money::multiply() instead of silently corrupting the total.
 */
final class TaxStep implements TotalStep
{
    /**
     * @param array<string, mixed> $context
     */
    public function apply(Money $running, array $context): Money
    {
        $rate = $context['tax_rate'] ?? 0.0;
        if (!\is_numeric($rate)) {
            return $running;
        }
        $rate = (float) $rate;
        if ($rate <= 0.0) {
            return $running;
        }

        return $running->multiply(1.0 + $rate);
    }
}
