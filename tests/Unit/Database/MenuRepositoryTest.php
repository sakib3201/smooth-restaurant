<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\Repositories\MenuItemRepository;
use SmoothRestaurant\Database\Repositories\MenuRepository;
use SmoothRestaurant\Database\Repositories\ModifierRepository;
use SmoothRestaurant\Tests\Unit\Database\Support\FakeWpdb;

/**
 * CRUD round-trip tests for the menu table repositories.
 *
 * Exercises insert → find → update → delete plus the menu-scoped list
 * queries against the in-memory FakeWpdb store. Every read goes through
 * BaseRepository::prepare(), asserted via the structured query log.
 */
final class MenuRepositoryTest extends TestCase
{
    private FakeWpdb $db;

    private MenuRepository $menus;

    private MenuItemRepository $items;

    private ModifierRepository $modifiers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new FakeWpdb();
        $this->menus = new MenuRepository($this->db);
        $this->items = new MenuItemRepository($this->db);
        $this->modifiers = new ModifierRepository($this->db);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function menuData(array $overrides = []): array
    {
        return array_merge(
            [
                'name' => 'Lunch',
                'slug' => 'lunch',
                'description' => 'Midday menu',
                'status' => 'publish',
                'sort_order' => 0,
            ],
            $overrides
        );
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function itemData(int $menuId, array $overrides = []): array
    {
        return array_merge(
            [
                'menu_id' => $menuId,
                'name' => 'Soup',
                'description' => 'Hot soup',
                'price_cents' => 950,
                'image_id' => 0,
                'status' => 'publish',
                'sort_order' => 0,
            ],
            $overrides
        );
    }

    public function test_menu_crud_round_trip(): void
    {
        $id = $this->menus->insert($this->menuData());

        $this->assertSame(1, $id);

        $row = $this->menus->findById($id);
        $this->assertNotNull($row);
        $this->assertSame('Lunch', $row['name']);
        $this->assertIsInt($row['id']);
        $this->assertNull($this->menus->findById(999));

        $this->assertTrue($this->menus->update($id, ['name' => 'Brunch']));
        $updated = $this->menus->findById($id);
        $this->assertNotNull($updated);
        $this->assertSame('Brunch', $updated['name']);
        $this->assertFalse($this->menus->update(999, ['name' => 'Ghost']));

        $this->assertTrue($this->menus->delete($id));
        $this->assertNull($this->menus->findById($id));
        $this->assertFalse($this->menus->delete($id));
    }

    public function test_menu_reads_go_through_prepare(): void
    {
        $id = $this->menus->insert($this->menuData());
        $this->menus->findById($id);
        $this->menus->paginate(1, 10);

        $logged = implode("\n", $this->db->queries);
        $this->assertStringContainsString('WHERE id = %d', $logged);
        $this->assertStringContainsString('LIMIT %d OFFSET %d', $logged);
    }

    public function test_paginate_returns_publish_in_display_order(): void
    {
        $this->menus->insert($this->menuData(['name' => 'Second', 'slug' => 'second', 'sort_order' => 2]));
        $this->menus->insert($this->menuData(['name' => 'First', 'slug' => 'first', 'sort_order' => 0]));
        $this->menus->insert(
            $this->menuData(['name' => 'Draft', 'slug' => 'draft', 'status' => 'draft', 'sort_order' => 0])
        );

        $page = $this->menus->paginate(1, 10);

        $this->assertSame(['First', 'Second'], array_column($page, 'name'));
    }

    public function test_paginate_pages_and_searches(): void
    {
        $this->menus->insert($this->menuData(['name' => 'Brunch', 'slug' => 'brunch']));
        $this->menus->insert(
            $this->menuData(['name' => 'Dinner', 'slug' => 'dinner', 'description' => 'Evening brunch specials'])
        );

        $this->assertSame(['Dinner'], array_column($this->menus->paginate(1, 10, 'evening'), 'name'));
        $this->assertCount(2, $this->menus->paginate(1, 10, 'brunch'));
        $this->assertSame(['Brunch'], array_column($this->menus->paginate(1, 1), 'name'));
        $this->assertSame(['Dinner'], array_column($this->menus->paginate(2, 1), 'name'));
        $this->assertSame(['Brunch', 'Dinner'], array_column($this->menus->paginate(0, 10), 'name'));
    }

    public function test_item_crud_and_list_by_menu(): void
    {
        $menuA = $this->menus->insert($this->menuData());
        $menuB = $this->menus->insert($this->menuData(['name' => 'Other', 'slug' => 'other']));

        $this->items->insert($this->itemData($menuA, ['name' => 'Beta', 'sort_order' => 1]));
        $itemId = $this->items->insert($this->itemData($menuA, ['name' => 'Alpha', 'sort_order' => 0]));
        $this->items->insert($this->itemData($menuA, ['name' => 'Hidden', 'status' => 'draft']));
        $this->items->insert($this->itemData($menuB, ['name' => 'Elsewhere']));

        $listed = $this->items->listByMenu($menuA);
        $this->assertSame(['Alpha', 'Beta'], array_column($listed, 'name'));
        $this->assertIsInt($listed[0]['price_cents']);

        $found = $this->items->findById($itemId);
        $this->assertNotNull($found);
        $this->assertSame(950, $found['price_cents']);

        $this->assertTrue($this->items->update($itemId, ['price_cents' => 1100]));
        $repriced = $this->items->findById($itemId);
        $this->assertNotNull($repriced);
        $this->assertSame(1100, $repriced['price_cents']);

        $this->assertTrue($this->items->delete($itemId));
        $this->assertNull($this->items->findById($itemId));
    }

    public function test_modifier_crud_and_list_by_item(): void
    {
        $menuId = $this->menus->insert($this->menuData());
        $itemId = $this->items->insert($this->itemData($menuId));
        $otherItem = $this->items->insert($this->itemData($menuId, ['name' => 'Salad']));

        $this->modifiers->insert(
            ['item_id' => $itemId, 'name' => 'Large', 'price_cents' => 200, 'sort_order' => 1]
        );
        $modifierId = $this->modifiers->insert(
            ['item_id' => $itemId, 'name' => 'Extra', 'price_cents' => 100, 'sort_order' => 0]
        );
        $this->modifiers->insert(
            ['item_id' => $otherItem, 'name' => 'Unrelated', 'price_cents' => 0, 'sort_order' => 0]
        );

        $listed = $this->modifiers->listByItem($itemId);
        $this->assertSame(['Extra', 'Large'], array_column($listed, 'name'));

        $found = $this->modifiers->findById($modifierId);
        $this->assertNotNull($found);
        $this->assertSame(100, $found['price_cents']);

        $this->assertTrue($this->modifiers->update($modifierId, ['price_cents' => 150]));
        $this->assertTrue($this->modifiers->delete($modifierId));
        $this->assertNull($this->modifiers->findById($modifierId));
    }

    public function test_generate_slug_collapses_and_falls_back(): void
    {
        $this->assertSame('summer-specials-2026', MenuRepository::generateSlug('Summer Specials 2026!'));
        $this->assertSame('lunch-menu', MenuRepository::generateSlug('  Lunch   Menu  '));
        $this->assertSame('menu', MenuRepository::generateSlug(''));
        $this->assertSame('menu', MenuRepository::generateSlug('---'));
    }
}
