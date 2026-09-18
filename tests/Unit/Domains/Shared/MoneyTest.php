<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Domains\Shared;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Domains\Shared\Money;

/**
 * Unit tests for the Money value object.
 *
 * Covers integer-exact arithmetic, float-factory rounding edges, and the
 * currency-mismatch guards that keep ledger totals honest.
 */
final class MoneyTest extends TestCase
{
    public function test_from_cents_round_trips(): void
    {
        $money = Money::fromCents(1299, 'USD');

        $this->assertSame(1299, $money->cents());
        $this->assertSame('USD', $money->currency());
        $this->assertSame(['cents' => 1299, 'currency' => 'USD'], $money->toArray());
    }

    public function test_currency_is_normalized_to_uppercase(): void
    {
        $this->assertSame('EUR', Money::fromCents(100, 'eur')->currency());
    }

    public function test_invalid_currency_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromCents(100, 'US');
    }

    public function test_zero_factory(): void
    {
        $zero = Money::zero('USD');

        $this->assertTrue($zero->equals(Money::fromCents(0, 'USD')));
    }

    public function test_add_and_sub(): void
    {
        $sum = Money::fromCents(1000, 'USD')->add(Money::fromCents(250, 'USD'));
        $difference = $sum->sub(Money::fromCents(300, 'USD'));

        $this->assertTrue($sum->equals(Money::fromCents(1250, 'USD')));
        $this->assertTrue($difference->equals(Money::fromCents(950, 'USD')));
    }

    public function test_add_with_currency_mismatch_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromCents(100, 'USD')->add(Money::fromCents(100, 'EUR'));
    }

    public function test_sub_with_currency_mismatch_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromCents(100, 'USD')->sub(Money::fromCents(100, 'EUR'));
    }

    public function test_from_float_converts_exact_decimals(): void
    {
        $this->assertSame(1999, Money::fromFloat(19.99, 'USD')->cents());
        $this->assertSame(-525, Money::fromFloat(-5.25, 'USD')->cents());
        $this->assertSame(0, Money::fromFloat(0.0, 'USD')->cents());
    }

    public function test_from_float_documents_binary_float_edge(): void
    {
        // 1.005 is stored as 1.0049999... in binary floating point, so the
        // half-up rounding of the stored value yields 100, not 101. Exact
        // amounts must use fromCents().
        $this->assertSame(100, Money::fromFloat(1.005, 'USD')->cents());
        $this->assertSame(1001, Money::fromFloat(10.005, 'USD')->cents());
    }

    public function test_from_float_rejects_non_finite(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromFloat(INF, 'USD');
    }

    public function test_from_float_rejects_nan(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromFloat(NAN, 'USD');
    }

    public function test_multiply_by_int_and_float(): void
    {
        $this->assertSame(300, Money::fromCents(100, 'USD')->multiply(3)->cents());
        $this->assertSame(2149, Money::fromCents(1999, 'USD')->multiply(1.075)->cents());
    }

    public function test_multiply_half_up_rounding(): void
    {
        // 5 cents halved is exactly 2.5: half-up rounds to 3.
        $this->assertSame(3, Money::fromCents(5, 'USD')->multiply(0.5)->cents());
        // 100 cents at a third is 33.33...: rounds down to 33.
        $this->assertSame(33, Money::fromCents(100, 'USD')->multiply(1 / 3)->cents());
    }

    public function test_multiply_honours_rounding_mode(): void
    {
        $halfDown = Money::fromCents(5, 'USD')->multiply(0.5, PHP_ROUND_HALF_DOWN);

        $this->assertSame(2, $halfDown->cents());
    }

    public function test_multiply_rejects_non_finite_factor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromCents(100, 'USD')->multiply(INF);
    }

    public function test_equals_compares_amount_and_currency(): void
    {
        $this->assertTrue(Money::fromCents(100, 'USD')->equals(Money::fromCents(100, 'USD')));
        $this->assertFalse(Money::fromCents(100, 'USD')->equals(Money::fromCents(101, 'USD')));
        $this->assertFalse(Money::fromCents(100, 'USD')->equals(Money::fromCents(100, 'EUR')));
    }
}
