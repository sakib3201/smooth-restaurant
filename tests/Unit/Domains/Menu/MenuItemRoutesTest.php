<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Domains\Menu;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\Repositories\MenuItemRepository;
use SmoothRestaurant\Database\Repositories\MenuRepository;
use SmoothRestaurant\Database\Repositories\ModifierRepository;
use SmoothRestaurant\Domains\Menu\MenuItemRoutes;
use SmoothRestaurant\Domains\Shared\DomainEvents;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Tests\Unit\Database\Support\FakeWpdb;

/**
 * Unit tests for the menu item REST controller.
 *
 * Callbacks run against plain param arrays (production passes the
 * WP_REST_Request, which the controller reads through get_params()).
 * Status codes are production edges applied through WP_REST_Response; unit
 * context asserts the payload arrays, and the Bruno contracts assert the
 * live codes.
 */
final class MenuItemRoutesTest extends TestCase
{
    private FakeWpdb $db;

    private MenuItemRoutes $routes;

    private MenuRepository $menus;

    private MenuItemRepository $items;

    private ModifierRepository $modifiers;

    /**
     * Payloads captured from the smooth_restaurant_menu_item_saved event.
     *
     * @var list<array<string, mixed>>
     */
    private array $itemEvents = [];

    /**
     * Payloads captured from the smooth_restaurant_menu_saved event.
     *
     * @var list<array<string, mixed>>
     */
    private array $menuEvents = [];

    protected function setUp(): void
    {
        parent::setUp();
        sr_test_reset_stubs();
        $this->db = new FakeWpdb();
        $this->menus = new MenuRepository($this->db);
        $this->items = new MenuItemRepository($this->db);
        $this->modifiers = new ModifierRepository($this->db);
        $this->routes = new MenuItemRoutes($this->menus, $this->items, $this->modifiers);
        add_action('smooth_restaurant_menu_item_saved', function (array $payload): void {
            $this->itemEvents[] = $payload;
        });
        add_action('smooth_restaurant_menu_saved', function (array $payload): void {
            $this->menuEvents[] = $payload;
        });
    }

    protected function tearDown(): void
    {
        sr_test_reset_stubs();
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function seedMenu(array $overrides = []): int
    {
        return $this->menus->insert(array_merge(
            [
                'name' => 'Lunch',
                'slug' => 'lunch-' . count($this->db->tables[$this->menus->getTable()] ?? []),
                'description' => 'Midday',
                'status' => 'publish',
                'sort_order' => 0,
            ],
            $overrides
        ));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function seedItem(int $menuId, array $overrides = []): int
    {
        return $this->items->insert(array_merge(
            [
                'menu_id' => $menuId,
                'name' => 'Soup',
                'description' => '',
                'price_cents' => 950,
                'image_id' => 0,
                'status' => 'publish',
                'sort_order' => 0,
            ],
            $overrides
        ));
    }

    /**
     * Registered route entries grouped by path.
     *
     * @return array<string, array<string, mixed>>
     */
    private function registeredRoutes(): array
    {
        $this->routes->register();
        $grouped = [];
        foreach ($GLOBALS['__sr_test_routes'] as $entry) {
            $this->assertIsArray($entry);
            $grouped[$entry['route']] = $entry;
        }

        return $grouped;
    }

    /**
     * Every permission callback across the item routes.
     *
     * @param array<string, array<string, mixed>> $routes
     * @return list<callable>
     */
    private function writeCallbacks(array $routes): array
    {
        $callbacks = [];
        foreach ($routes as $entry) {
            foreach ($entry['args'] as $endpoint) {
                if (is_array($endpoint) && isset($endpoint['permission_callback'])) {
                    $callbacks[] = $endpoint['permission_callback'];
                }
            }
        }

        return $callbacks;
    }

    public function test_registers_collection_single_and_order_routes(): void
    {
        $routes = $this->registeredRoutes();

        $this->assertArrayHasKey('/menus/(?P<menu_id>\d+)/items', $routes);
        $this->assertArrayHasKey('/items/(?P<id>\d+)', $routes);
        $this->assertArrayHasKey('/menus/(?P<menu_id>\d+)/items/order', $routes);
        $this->assertSame('smooth/v1', $routes['/items/(?P<id>\d+)']['namespace']);
    }

    public function test_writes_require_the_manage_capability(): void
    {
        $callbacks = $this->writeCallbacks($this->registeredRoutes());

        $this->assertCount(4, $callbacks);
        foreach ($callbacks as $callback) {
            $this->assertIsCallable($callback);
            $this->assertFalse($callback());
        }

        sr_test_grant_caps([MenuProvider::MANAGE_CAP]);
        foreach ($callbacks as $callback) {
            $this->assertTrue($callback());
        }
    }

    public function test_create_round_trip_appends_and_fires_events(): void
    {
        $menuId = $this->seedMenu();

        $first = $this->routes->createItem(['menu_id' => $menuId, 'name' => 'Soup']);
        $this->assertIsArray($first);
        $this->assertSame('Soup', $first['data']['name']);
        $this->assertSame('publish', $first['data']['status']);
        $this->assertSame(0, $first['data']['price_cents']);
        $this->assertSame(0, $first['data']['sort_order']);

        $second = $this->routes->createItem(['menu_id' => $menuId, 'name' => 'Salad', 'status' => 'draft']);
        $this->assertIsArray($second);
        $this->assertSame(1, $second['data']['sort_order']);
        $this->assertSame('draft', $second['data']['status']);

        $this->assertCount(2, $this->itemEvents);
        $this->assertSame('created', $this->itemEvents[0]['action']);
        $this->assertSame((int) $first['data']['id'], (int) $this->itemEvents[0]['id']);
        $this->assertSame($menuId, (int) $this->itemEvents[0]['menu_id']);
        $this->assertCount(2, $this->menuEvents);
        $this->assertSame('item', $this->menuEvents[0]['reason']);
        $this->assertSame('created', $this->menuEvents[0]['action']);
        $this->assertSame($menuId, (int) $this->menuEvents[0]['id']);
    }

    public function test_create_validates_parent_name_and_money(): void
    {
        $menuId = $this->seedMenu();

        $unknown = $this->routes->createItem(['menu_id' => 999, 'name' => 'Ghost']);
        $this->assertIsArray($unknown);
        $this->assertSame('smooth_menu_not_found', $unknown['code']);
        $this->assertSame(404, $unknown['data']['status']);

        $nameless = $this->routes->createItem(['menu_id' => $menuId, 'description' => 'No name']);
        $this->assertIsArray($nameless);
        $this->assertSame('smooth_menu_item_missing_name', $nameless['code']);
        $this->assertSame(400, $nameless['data']['status']);

        $priceless = $this->routes->createItem(['menu_id' => $menuId, 'name' => 'Soup', 'price_cents' => -50]);
        $this->assertIsArray($priceless);
        $this->assertSame('smooth_menu_item_invalid_price', $priceless['code']);

        $floatPrice = $this->routes->createItem(['menu_id' => $menuId, 'name' => 'Soup', 'price_cents' => 9.5]);
        $this->assertIsArray($floatPrice);
        $this->assertSame('smooth_menu_item_invalid_price', $floatPrice['code']);

        $badImage = $this->routes->createItem(['menu_id' => $menuId, 'name' => 'Soup', 'image_id' => -1]);
        $this->assertIsArray($badImage);
        $this->assertSame('smooth_menu_item_invalid_image', $badImage['code']);

        $this->assertSame([], $this->items->listByMenu($menuId, 'publish'));
        $this->assertSame([], $this->items->listByMenu($menuId, 'draft'));
        $this->assertSame([], $this->itemEvents);
    }

    public function test_update_touches_only_present_fields(): void
    {
        $menuId = $this->seedMenu();
        $otherMenu = $this->seedMenu(['name' => 'Dinner', 'slug' => 'dinner']);
        $itemId = $this->seedItem($menuId);

        $missing = $this->routes->updateItem(['id' => 999, 'name' => 'Ghost']);
        $this->assertIsArray($missing);
        $this->assertSame('smooth_menu_item_not_found', $missing['code']);

        $empty = $this->routes->updateItem(['id' => $itemId, 'name' => '  ']);
        $this->assertIsArray($empty);
        $this->assertSame('smooth_menu_item_missing_name', $empty['code']);

        $badPrice = $this->routes->updateItem(['id' => $itemId, 'price_cents' => -1]);
        $this->assertIsArray($badPrice);
        $this->assertSame('smooth_menu_item_invalid_price', $badPrice['code']);

        $updated = $this->routes->updateItem([
            'id' => $itemId,
            'name' => 'Chowder',
            'status' => 'draft',
            'menu_id' => $otherMenu,
        ]);
        $this->assertIsArray($updated);
        $this->assertSame('Chowder', $updated['data']['name']);
        $this->assertSame('draft', $updated['data']['status']);
        $this->assertSame(950, $updated['data']['price_cents']);
        $this->assertSame($menuId, (int) $updated['data']['menu_id']);

        $this->assertCount(1, $this->itemEvents);
        $this->assertSame('updated', $this->itemEvents[0]['action']);
        $this->assertSame($itemId, (int) $this->itemEvents[0]['id']);
        $this->assertCount(1, $this->menuEvents);
        $this->assertSame('item', $this->menuEvents[0]['reason']);
    }

    public function test_delete_cascades_both_statuses_and_fires_events(): void
    {
        $menuId = $this->seedMenu();
        $itemId = $this->seedItem($menuId);
        $otherItem = $this->seedItem($menuId, ['name' => 'Salad', 'sort_order' => 1]);
        $this->modifiers->insert(
            ['item_id' => $itemId, 'name' => 'Large', 'price_cents' => 200, 'status' => 'publish', 'sort_order' => 0]
        );
        $this->modifiers->insert(
            ['item_id' => $itemId, 'name' => 'Paused', 'price_cents' => 0, 'status' => 'draft', 'sort_order' => 1]
        );
        $kept = $this->modifiers->insert(
            [
                'item_id' => $otherItem, 'name' => 'Kept', 'price_cents' => 0,
                'status' => 'publish', 'sort_order' => 0,
            ]
        );

        $missing = $this->routes->deleteItem(['id' => 999]);
        $this->assertIsArray($missing);
        $this->assertSame('smooth_menu_item_not_found', $missing['code']);

        $deleted = $this->routes->deleteItem(['id' => $itemId]);
        $this->assertIsArray($deleted);
        $this->assertSame(['deleted' => true, 'id' => $itemId], $deleted['data']);
        $this->assertNull($this->items->findById($itemId));
        $this->assertSame([], $this->modifiers->listByItem($itemId));
        $this->assertSame([], $this->modifiers->listByItem($itemId, 'draft'));
        $this->assertNotNull($this->modifiers->findById($kept));

        $this->assertCount(1, $this->itemEvents);
        $this->assertSame('deleted', $this->itemEvents[0]['action']);
        $this->assertCount(1, $this->menuEvents);
        $this->assertSame('deleted', $this->menuEvents[0]['action']);
    }

    public function test_reorder_assigns_dense_order_and_rejects_stale_sets(): void
    {
        $menuId = $this->seedMenu();
        $first = $this->seedItem($menuId, ['name' => 'Alpha', 'sort_order' => 0]);
        $second = $this->seedItem($menuId, ['name' => 'Beta', 'sort_order' => 5]);
        $third = $this->seedItem($menuId, ['name' => 'Gamma', 'status' => 'draft', 'sort_order' => 9]);

        $unknown = $this->routes->reorderItems(['menu_id' => 999, 'ids' => [$first]]);
        $this->assertIsArray($unknown);
        $this->assertSame('smooth_menu_not_found', $unknown['code']);

        $missing = $this->routes->reorderItems(['menu_id' => $menuId, 'ids' => [$first, $second]]);
        $this->assertIsArray($missing);
        $this->assertSame('smooth_menu_item_order_mismatch', $missing['code']);
        $this->assertSame(400, $missing['data']['status']);

        $foreign = $this->routes->reorderItems(['menu_id' => $menuId, 'ids' => [$first, $second, 999]]);
        $this->assertIsArray($foreign);
        $this->assertSame('smooth_menu_item_order_mismatch', $foreign['code']);

        $shapeless = $this->routes->reorderItems(['menu_id' => $menuId, 'ids' => 'nope']);
        $this->assertIsArray($shapeless);
        $this->assertSame('smooth_menu_item_order_mismatch', $shapeless['code']);

        $orders = [];
        foreach (['publish', 'draft'] as $status) {
            foreach ($this->items->listByMenu($menuId, $status) as $row) {
                $orders[(int) $row['id']] = (int) $row['sort_order'];
            }
        }
        $this->assertSame([0, 5, 9], [$orders[$first], $orders[$second], $orders[$third]]);
        $this->assertSame([], $this->itemEvents);

        $reordered = $this->routes->reorderItems(['menu_id' => $menuId, 'ids' => [$third, $second, $first]]);
        $this->assertIsArray($reordered);
        $this->assertSame([$third, $second, $first], $reordered['data']['ids']);

        $names = [];
        foreach (['publish', 'draft'] as $status) {
            foreach ($this->items->listByMenu($menuId, $status) as $row) {
                $names[(int) $row['sort_order']] = $row['name'];
            }
        }
        \ksort($names);
        $this->assertSame(['Gamma', 'Beta', 'Alpha'], array_values($names));

        $reads = [];
        foreach ($this->items->listByMenu($menuId, 'publish') as $row) {
            $reads[] = $row['name'];
        }
        $this->assertSame(['Beta', 'Alpha'], $reads);

        $this->assertCount(1, $this->itemEvents);
        $this->assertSame('reordered', $this->itemEvents[0]['action']);
        $this->assertCount(1, $this->menuEvents);
        $this->assertSame('reordered', $this->menuEvents[0]['action']);
        $this->assertSame($menuId, (int) $this->menuEvents[0]['id']);
    }

    public function test_response_rows_match_the_declared_schema(): void
    {
        $menuId = $this->seedMenu();
        $this->seedItem($menuId);
        $schema = $this->routes->itemSchema();

        $response = $this->routes->createItem(['menu_id' => $menuId, 'name' => 'Chowder']);
        $this->assertIsArray($response);

        $allowed = array_keys($schema['properties']);
        foreach (array_keys($response['data']) as $field) {
            $this->assertContains(
                $field,
                $allowed,
                sprintf('Response field "%s" is missing from itemSchema().', $field)
            );
        }
        foreach ($schema['required'] as $field) {
            $this->assertArrayHasKey($field, $response['data']);
        }
    }

    public function test_saved_event_name_is_namespaced(): void
    {
        $this->assertSame('smooth_restaurant_menu_item_saved', DomainEvents::MENU_ITEM_SAVED);
    }
}
