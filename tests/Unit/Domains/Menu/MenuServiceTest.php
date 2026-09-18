<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Domains\Menu;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Domains\Menu\MenuService;

/**
 * Unit tests for the pure menu domain service.
 */
final class MenuServiceTest extends TestCase
{
    public function test_cache_key_namespaces_blog_scope_id_and_field(): void
    {
        $this->assertSame(
            'smooth:3:binding:12:item/name:34',
            MenuService::cacheKey(3, 'binding', 12, 'item/name:34')
        );
        $this->assertNotSame(
            MenuService::cacheKey(1, 'binding', 12, 'item/name:34'),
            MenuService::cacheKey(2, 'binding', 12, 'item/name:34')
        );
    }

    public function test_remember_computes_once_per_key(): void
    {
        $service = new MenuService();
        $runs = 0;
        $compute = static function () use (&$runs): string {
            $runs++;
            return 'value';
        };

        $this->assertSame('value', $service->remember('k', $compute));
        $this->assertSame('value', $service->remember('k', $compute));
        $this->assertSame(1, $runs);
    }

    public function test_remember_keeps_keys_independent(): void
    {
        $service = new MenuService();

        $this->assertSame('a', $service->remember('a', static fn (): string => 'a'));
        $this->assertSame('b', $service->remember('b', static fn (): string => 'b'));
    }

    public function test_flush_clears_the_memo(): void
    {
        $service = new MenuService();
        $runs = 0;
        $compute = static function () use (&$runs): int {
            $runs++;
            return $runs;
        };

        $this->assertSame(1, $service->remember('k', $compute));
        $service->flush();
        $this->assertSame(2, $service->remember('k', $compute));
    }

    public function test_format_price_renders_cents_as_decimal(): void
    {
        $this->assertSame('9.50', MenuService::formatPrice(950));
        $this->assertSame('0.00', MenuService::formatPrice(0));
        $this->assertSame('0.05', MenuService::formatPrice(5));
    }

    public function test_assemble_groups_modifiers_under_items(): void
    {
        $tree = MenuService::assemble(
            ['id' => 7, 'name' => 'Lunch'],
            [
                ['id' => 1, 'menu_id' => 7, 'name' => 'Soup'],
                ['id' => 2, 'menu_id' => 7, 'name' => 'Salad'],
            ],
            [
                ['id' => 9, 'item_id' => 1, 'name' => 'Large'],
                ['id' => 10, 'item_id' => 9, 'name' => 'Stray'],
            ]
        );

        $this->assertSame(['id' => 7, 'name' => 'Lunch'], $tree['menu']);
        $this->assertSame(['Soup', 'Salad'], [$tree['items'][0]['item']['name'], $tree['items'][1]['item']['name']]);
        $this->assertSame(['Large'], array_column($tree['items'][0]['modifiers'], 'name'));
        $this->assertSame([], $tree['items'][1]['modifiers']);
    }
}
