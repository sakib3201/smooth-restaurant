<?php

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Checkout;

use SmoothRestaurant\Domains\Shared\Money;

/**
 * Composable checkout totals pipeline.
 *
 * Runs an ordered list of TotalStep stages over a starting total. The
 * default order is line items, then discount, then tax, then fee — so tax
 * applies to the discounted subtotal and fees stay outside the tax base.
 * Custom pipelines pass their own ordered steps to the constructor; the
 * active step list is filterable at calculation time via the
 * `smooth_checkout_total_steps` filter (entries that do not implement
 * TotalStep are ignored).
 */
final class TotalsCalculator
{
    /**
     * @var list<TotalStep>
     */
    private array $steps;

    /**
     * @param list<TotalStep>|null $steps Ordered steps; null selects the default line, discount, tax, fee order.
     * @throws \InvalidArgumentException When a step does not implement TotalStep.
     */
    public function __construct(?array $steps = null)
    {
        $steps ??= self::defaultSteps();
        foreach ($steps as $step) {
            if (!$step instanceof TotalStep) {
                throw new \InvalidArgumentException('TotalsCalculator steps must implement TotalStep.');
            }
        }

        $this->steps = \array_values($steps);
    }

    /**
     * Default pipeline: line items, discount, tax, fee.
     *
     * @return list<TotalStep>
     */
    public static function defaultSteps(): array
    {
        return [
            new LineItemStep(),
            new DiscountStep(),
            new TaxStep(),
            new FeeStep(),
        ];
    }

    /**
     * Configured steps, before the `smooth_checkout_total_steps` filter.
     *
     * @return list<TotalStep>
     */
    public function steps(): array
    {
        return $this->steps;
    }

    /**
     * Run the pipeline over a starting total.
     *
     * @param array<string, mixed> $context Calculation inputs (items, discount_cents, tax_rate, fee_cents).
     */
    public function calculate(Money $starting, array $context = []): Money
    {
        $steps = $this->steps;
        if (\function_exists('apply_filters')) {
            /**
             * Filter the active totals pipeline.
             *
             * @param list<TotalStep>      $steps   Ordered steps to run.
             * @param array<string, mixed> $context Calculation inputs.
             */
            $filtered = \apply_filters('smooth_checkout_total_steps', $steps, $context);
            $steps = [];
            if (\is_array($filtered)) {
                foreach ($filtered as $candidate) {
                    if ($candidate instanceof TotalStep) {
                        $steps[] = $candidate;
                    }
                }
            }
        }

        $running = $starting;
        foreach ($steps as $step) {
            $running = $step->apply($running, $context);
        }

        return $running;
    }
}
