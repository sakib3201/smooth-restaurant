<?php

/**
 * Unit tests for the Settings service.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Core;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Settings;

/**
 * Class SettingsTest
 *
 * WordPress is not loaded in unit context, so these tests exercise the
 * defaults surface plus the in-memory fallback path.
 */
class SettingsTest extends TestCase
{
    /**
     * Test that defaults expose the documented keys.
     *
     * @return void
     */
    public function test_defaults_expose_documented_keys(): void
    {
        $settings = new Settings();

        $this->assertSame('USD', $settings->get('smooth_currency'));
        $this->assertSame('site', $settings->get('smooth_timezone_mode'));
        $this->assertTrue($settings->get('smooth_guest_checkout'));
    }

    /**
     * Test that all() merges stored values over defaults.
     *
     * @return void
     */
    public function test_all_returns_defaults_when_nothing_stored(): void
    {
        $all = (new Settings())->all();

        $this->assertSame(Settings::DEFAULTS, $all);
    }

    /**
     * Test that unknown keys throw on read and write.
     *
     * @return void
     */
    public function test_unknown_key_throws(): void
    {
        $settings = new Settings();

        $this->expectException(InvalidArgumentException::class);
        $settings->get('smooth_nope');
    }

    /**
     * Test that set() with an unknown key throws.
     *
     * @return void
     */
    public function test_set_unknown_key_throws(): void
    {
        $settings = new Settings();

        $this->expectException(InvalidArgumentException::class);
        $settings->set('smooth_nope', 'x');
    }

    /**
     * Test that set() validates the value type against the default.
     *
     * @return void
     */
    public function test_set_type_mismatch_throws(): void
    {
        $settings = new Settings();

        $this->expectException(InvalidArgumentException::class);
        $settings->set('smooth_guest_checkout', 'yes');
    }

    /**
     * Test that set() round-trips through the in-memory fallback.
     *
     * @return void
     */
    public function test_set_get_round_trip(): void
    {
        $settings = new Settings();
        $settings->set('smooth_currency', 'EUR');
        $settings->set('smooth_guest_checkout', false);

        $this->assertSame('EUR', $settings->get('smooth_currency'));
        $this->assertFalse($settings->get('smooth_guest_checkout'));
        // Untouched defaults still resolve.
        $this->assertSame('site', $settings->get('smooth_timezone_mode'));
    }
}
