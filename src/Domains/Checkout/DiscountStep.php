<?php

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Checkout;

use SmoothRestaurant\Domains\Shared\Money;

/**
 * Subtracts a coupon/discount and floors the running total at zero.
 *
 * Reads $context['discount_cents'] (int, default 0). Non-positive discounts
 * are a no-op; a discount larger than the subtotal floors at zero instead of
 * going negative, so an over-generous coupon can never credit the diner.
 */
final class DiscountStep implements TotalStep
{
    /**
     * @param array<string, mixed> $context
     */
    public function apply(Money $running, array $context): Money
    {
        $discount = (int) ($context['discount_cents'] ?? 0);
        if ($discount <= 0) {
            return $running;
        }

        return Money::fromCents(\max(0, $running->cents() - $discount), $running->currency());
    }
}
