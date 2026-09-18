<?php

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Checkout;

use SmoothRestaurant\Domains\Shared\Money;

/**
 * Adds a flat fee (e.g. service or delivery fee) to the running total.
 *
 * Reads $context['fee_cents'] (int, default 0). Non-positive fees are a
 * no-op: fee lines only ever increase the total.
 */
final class FeeStep implements TotalStep
{
    /**
     * @param array<string, mixed> $context
     */
    public function apply(Money $running, array $context): Money
    {
        $fee = (int) ($context['fee_cents'] ?? 0);
        if ($fee <= 0) {
            return $running;
        }

        return $running->add(Money::fromCents($fee, $running->currency()));
    }
}
