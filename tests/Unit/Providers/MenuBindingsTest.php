<?php

/**
 * Unit tests for the smooth/menu block bindings source.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Contracts\MenuItemRepositoryInterface;
use SmoothRestaurant\Contracts\MenuRepositoryInterface;
use SmoothRestaurant\Contracts\ModifierRepositoryInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\Context;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Database\Repositories\MenuItemRepository;
use SmoothRestaurant\Database\Repositories\MenuRepository;
use SmoothRestaurant\Database\Repositories\ModifierRepository;
use SmoothRestaurant\Domains\Menu\MenuService;
use SmoothRestaurant\Providers\BlocksProvider;
use SmoothRestaurant\Tests\Unit\Database\Support\FakeWpdb;

/**
 * Class MenuBindingsTest
 *
 * The bindings source is read-only and live-reads the custom tables, so
 * reorder, inline edits, and autosave (repeated renders) always reflect the
 * current table state. Unknown keys and missing rows resolve to null so the
 * block falls back to its static content.
 */
class MenuBindingsTest extends TestCase
{
    private FakeWpdb $db;

    private Container $container;

    private BlocksProvider $provider;

    private int $menuId;

    private int $itemId;

    /**
     * Set up container-backed repositories and seed one menu + item + modifier.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Plugin::reset();
        Context::reset();
        sr_test_reset_stubs();

        $this->db = new FakeWpdb();
        $container = new Container();
        $container->instance(MenuRepositoryInterface::class, new MenuRepository($this->db));
        $container->instance(MenuItemRepositoryInterface::class, new MenuItemRepository($this->db));
        $container->instance(ModifierRepositoryInterface::class, new ModifierRepository($this->db));
        $container->singleton(MenuService::class);
        $container->register(BlocksProvider::class);
        $this->container = $container;

        $providers = $container->providers();
        $provider = $providers[0];
        $this->assertInstanceOf(BlocksProvider::class, $provider);
        $this->provider = $provider;

        $menus = new MenuRepository($this->db);
        $items = new MenuItemRepository($this->db);
        $modifiers = new ModifierRepository($this->db);
        $this->menuId = $menus->insert([
            'name' => 'Lunch', 'slug' => 'lunch', 'description' => 'Midday',
            'status' => 'publish', 'sort_order' => 0,
        ]);
        $this->itemId = $items->insert([
            'menu_id' => $this->menuId, 'name' => 'Soup', 'description' => 'Hot soup',
            'price_cents' => 950, 'image_id' => 0, 'status' => 'publish', 'sort_order' => 0,
        ]);
        $modifiers->insert([
            'item_id' => $this->itemId, 'name' => 'Large', 'price_cents' => 200, 'sort_order' => 0,
        ]);
    }

    /**
     * Tear down stub state and the plugin singleton after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        sr_test_reset_stubs();
        Context::reset();
        Plugin::reset();
        parent::tearDown();
    }

    /**
     * Build a block-shaped context carrier.
     *
     * @return array<string, mixed>
     */
    private function block(): array
    {
        return ['context' => ['smooth/menuId' => $this->menuId, 'smooth/itemId' => $this->itemId]];
    }

    /**
     * Test that registration declares the contracted source shape.
     *
     * @return void
     */
    public function test_registers_contracted_source_shape(): void
    {
        $this->provider->registerBlocks();

        $bindings = $GLOBALS['__sr_test_bindings'];
        $this->assertIsArray($bindings);
        $this->assertArrayHasKey(BlocksProvider::BINDINGS_SOURCE, $bindings);
        $source = $bindings[BlocksProvider::BINDINGS_SOURCE];
        $this->assertSame('smooth/menu', BlocksProvider::BINDINGS_SOURCE);
        $this->assertSame(['smooth/menuId', 'smooth/itemId'], $source['use_context']);
        $this->assertIsCallable($source['get_value_callback']);
    }

    /**
     * Test menu-level key resolution.
     *
     * @return void
     */
    public function test_resolves_menu_keys(): void
    {
        $this->assertSame(
            'Lunch',
            $this->provider->getBindingValue(['key' => 'menu/name'], $this->block(), 'content')
        );
        $this->assertSame(
            'Midday',
            $this->provider->getBindingValue(['key' => 'menu/description'], $this->block(), 'content')
        );
    }

    /**
     * Test item and modifier key resolution incl. price formatting.
     *
     * @return void
     */
    public function test_resolves_item_and_modifier_keys(): void
    {
        $block = $this->block();

        $this->assertSame('Soup', $this->provider->getBindingValue(['key' => 'item/name'], $block, 'content'));
        $this->assertSame(
            'Hot soup',
            $this->provider->getBindingValue(['key' => 'item/description'], $block, 'content')
        );
        $this->assertSame('9.50', $this->provider->getBindingValue(['key' => 'item/price'], $block, 'content'));
        $this->assertSame(950, $this->provider->getBindingValue(['key' => 'item/price_raw'], $block, 'content'));
        $this->assertSame(
            'Large',
            $this->provider->getBindingValue(['key' => 'modifier/name'], $block, 'content')
        );
        $this->assertSame('2.00', $this->provider->getBindingValue(['key' => 'modifier/price'], $block, 'content'));
    }

    /**
     * Test that repeated renders track live table state (autosave-safe).
     *
     * @return void
     */
    public function test_repeated_renders_track_live_state(): void
    {
        $block = $this->block();
        $args = ['key' => 'item/name'];

        $this->assertSame('Soup', $this->provider->getBindingValue($args, $block, 'content'));
        $this->assertSame('Soup', $this->provider->getBindingValue($args, $block, 'content'));

        $items = new MenuItemRepository($this->db);
        $items->update($this->itemId, ['name' => 'Bisque']);

        // Same process = same request: the write above and the render below
        // would never share a request in production (writes go through REST),
        // so flush the per-request memo to simulate the next request.
        $service = $this->container->make(MenuService::class);
        $this->assertInstanceOf(MenuService::class, $service);
        $service->flush();

        $this->assertSame('Bisque', $this->provider->getBindingValue($args, $block, 'content'));
    }

    /**
     * Test that repeated renders in one request hit the per-request memo.
     *
     * @return void
     */
    public function test_repeated_renders_reuse_the_memo(): void
    {
        $block = $this->block();
        $args = ['key' => 'item/name'];

        $this->provider->getBindingValue($args, $block, 'content');
        $queriesAfterFirst = count($this->db->queries);

        $this->provider->getBindingValue($args, $block, 'content');

        $this->assertSame($queriesAfterFirst, count($this->db->queries));
        $this->assertGreaterThan(0, $queriesAfterFirst);
    }

    /**
     * Test that unknown keys and missing rows resolve to null.
     *
     * @return void
     */
    public function test_unknown_keys_and_missing_rows_resolve_to_null(): void
    {
        $block = $this->block();

        $this->assertNull($this->provider->getBindingValue(['key' => 'bogus/key'], $block, 'content'));
        $this->assertNull($this->provider->getBindingValue([], $block, 'content'));
        $this->assertNull($this->provider->getBindingValue(['key' => 'item/name'], [], 'content'));
        $this->assertNull(
            $this->provider->getBindingValue(
                ['key' => 'item/name'],
                ['context' => ['smooth/menuId' => 999, 'smooth/itemId' => $this->itemId]],
                'content'
            )
        );
        $this->assertNull(
            $this->provider->getBindingValue(
                ['key' => 'item/name'],
                ['context' => ['smooth/menuId' => $this->menuId, 'smooth/itemId' => 999]],
                'content'
            )
        );
    }

    /**
     * Test that items from another menu do not leak across contexts.
     *
     * @return void
     */
    public function test_item_menu_mismatch_resolves_to_null(): void
    {
        $menus = new MenuRepository($this->db);
        $otherId = $menus->insert([
            'name' => 'Dinner', 'slug' => 'dinner', 'description' => '',
            'status' => 'publish', 'sort_order' => 1,
        ]);

        $this->assertNull(
            $this->provider->getBindingValue(
                ['key' => 'item/name'],
                ['context' => ['smooth/menuId' => $otherId, 'smooth/itemId' => $this->itemId]],
                'content'
            )
        );
    }
}
