<?php

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Checkout;

use SmoothRestaurant\Domains\Shared\Money;

/**
 * Sums cart line items onto the running total.
 *
 * Reads $context['items'] as a list of ['unit_cents' => int, 'qty' => int]
 * entries ('quantity' is accepted as an alias; qty defaults to 1). Entries
 * that are not arrays are skipped; amounts take the running total's currency.
 */
final class LineItemStep implements TotalStep
{
    /**
     * @param array<string, mixed> $context
     */
    public function apply(Money $running, array $context): Money
    {
        $items = $context['items'] ?? [];
        if (!\is_array($items)) {
            return $running;
        }

        $total = 0;
        foreach ($items as $item) {
            if (!\is_array($item)) {
                continue;
            }
            $unit = isset($item['unit_cents']) ? (int) $item['unit_cents'] : 0;
            $qty = isset($item['qty']) ? (int) $item['qty'] : (isset($item['quantity']) ? (int) $item['quantity'] : 1);
            $total += $unit * $qty;
        }

        if (0 === $total) {
            return $running;
        }

        return $running->add(Money::fromCents($total, $running->currency()));
    }
}
