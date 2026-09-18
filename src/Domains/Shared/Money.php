<?php

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Shared;

/**
 * Immutable money value object: integer minor units plus ISO currency code.
 *
 * All arithmetic stays in integer cents so ledger totals never drift on
 * binary floats. Floats enter only through the explicit fromFloat() factory
 * (and are rounded half-up there); nothing else accepts or returns a float.
 * Currency codes are normalized to uppercase and must be 3 ASCII letters.
 */
final readonly class Money
{
    private function __construct(
        private int $cents,
        private string $currency
    ) {
    }

    /**
     * Create money from integer minor units (e.g. cents).
     *
     * @throws \InvalidArgumentException When the currency code is not 3 letters.
     */
    public static function fromCents(int $cents, string $currency): self
    {
        return new self($cents, self::normalizeCurrency($currency));
    }

    /**
     * Create money from major units (e.g. dollars), rounding half-up to cents.
     *
     * Binary floats cannot represent every decimal fraction: 1.005 is stored
     * as 1.0049999..., so fromFloat(1.005, 'USD') yields 100 cents, not 101.
     * Prefer fromCents() for exact amounts; use this factory only at trust
     * boundaries where floats arrive from outside (settings, APIs).
     *
     * @throws \InvalidArgumentException When the amount is non-finite or the currency code is not 3 letters.
     */
    public static function fromFloat(float $amount, string $currency): self
    {
        if (!\is_finite($amount)) {
            throw new \InvalidArgumentException('Money amount must be finite.');
        }

        return new self((int) \round($amount * 100, 0, \PHP_ROUND_HALF_UP), self::normalizeCurrency($currency));
    }

    /**
     * Zero amount in the given currency.
     *
     * @throws \InvalidArgumentException When the currency code is not 3 letters.
     */
    public static function zero(string $currency): self
    {
        return new self(0, self::normalizeCurrency($currency));
    }

    /**
     * Minor units.
     */
    public function cents(): int
    {
        return $this->cents;
    }

    /**
     * Uppercase 3-letter ISO currency code.
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Add two amounts in the same currency.
     *
     * @throws \InvalidArgumentException When currencies differ.
     */
    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->cents + $other->cents, $this->currency);
    }

    /**
     * Subtract an amount in the same currency (may go negative; callers floor).
     *
     * @throws \InvalidArgumentException When currencies differ.
     */
    public function sub(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->cents - $other->cents, $this->currency);
    }

    /**
     * Scale by a factor, rounding to whole cents.
     *
     * @param int|float $factor   Multiplier (e.g. 2, or 1.075 for +7.5% tax).
     * @param 1|2|3|4   $rounding One of the PHP_ROUND_* constants.
     * @throws \InvalidArgumentException When a float factor is non-finite.
     */
    public function multiply(int|float $factor, int $rounding = \PHP_ROUND_HALF_UP): self
    {
        if (\is_float($factor) && !\is_finite($factor)) {
            throw new \InvalidArgumentException('Money factor must be finite.');
        }

        return new self((int) \round($this->cents * $factor, 0, $rounding), $this->currency);
    }

    /**
     * Whether both amount and currency match.
     */
    public function equals(self $other): bool
    {
        return $this->cents === $other->cents && $this->currency === $other->currency;
    }

    /**
     * Serialize to primitives (cents stay integers; no floats leak).
     *
     * @return array{cents: int, currency: string}
     */
    public function toArray(): array
    {
        return [
            'cents'    => $this->cents,
            'currency' => $this->currency,
        ];
    }

    /**
     * Normalize and validate a currency code.
     *
     * @throws \InvalidArgumentException When the code is not 3 ASCII letters.
     */
    private static function normalizeCurrency(string $currency): string
    {
        $code = \strtoupper($currency);
        if (1 !== \preg_match('/^[A-Z]{3}$/', $code)) {
            throw new \InvalidArgumentException('Money currency must be a 3-letter code.');
        }

        return $code;
    }

    /**
     * Guard mixed-currency arithmetic.
     *
     * @throws \InvalidArgumentException When currencies differ.
     */
    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                \sprintf('Currency mismatch: %s vs %s.', $this->currency, $other->currency)
            );
        }
    }
}
