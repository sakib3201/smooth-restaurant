<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Domains\Menu;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\Repositories\MenuItemRepository;
use SmoothRestaurant\Database\Repositories\MenuRepository;
use SmoothRestaurant\Database\Repositories\ModifierRepository;
use SmoothRestaurant\Domains\Menu\ModifierRoutes;
use SmoothRestaurant\Domains\Shared\DomainEvents;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Tests\Unit\Database\Support\FakeWpdb;

/**
 * Unit tests for the modifier REST controller.
 *
 * Callbacks run against plain param arrays (production passes the
 * WP_REST_Request, which the controller reads through get_params()).
 * Status codes are production edges applied through WP_REST_Response; unit
 * context asserts the payload arrays, and the Bruno contracts assert the
 * live codes.
 */
final class ModifierRoutesTest extends TestCase
{
    private FakeWpdb $db;

    private ModifierRoutes $routes;

    private MenuRepository $menus;

    private MenuItemRepository $items;

    private ModifierRepository $modifiers;

    /**
     * Payloads captured from the smooth_restaurant_modifier_saved event.
     *
     * @var list<array<string, mixed>>
     */
    private array $modifierEvents = [];

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
        $this->routes = new ModifierRoutes($this->items, $this->modifiers);
        add_action('smooth_restaurant_modifier_saved', function (array $payload): void {
            $this->modifierEvents[] = $payload;
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
     * @param array<string, mixed> $overrides
     */
    private function seedModifier(int $itemId, array $overrides = []): int
    {
        return $this->modifiers->insert(array_merge(
            [
                'item_id' => $itemId,
                'name' => 'Extra',
                'price_cents' => 100,
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

    public function test_registers_collection_single_and_order_routes(): void
    {
        $routes = $this->registeredRoutes();

        $this->assertArrayHasKey('/items/(?P<item_id>\d+)/modifiers', $routes);
        $this->assertArrayHasKey('/modifiers/(?P<id>\d+)', $routes);
        $this->assertArrayHasKey('/items/(?P<item_id>\d+)/modifiers/order', $routes);
        $this->assertSame('smooth/v1', $routes['/modifiers/(?P<id>\d+)']['namespace']);
    }

    public function test_writes_require_the_manage_capability(): void
    {
        $routes = $this->registeredRoutes();

        $callbacks = [];
        foreach ($routes as $entry) {
            foreach ($entry['args'] as $endpoint) {
                if (is_array($endpoint) && isset($endpoint['permission_callback'])) {
                    $callbacks[] = $endpoint['permission_callback'];
                }
            }
        }

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
        $itemId = $this->seedItem($menuId);

        $first = $this->routes->createModifier(['item_id' => $itemId, 'name' => 'Extra', 'price_cents' => 100]);
        $this->assertIsArray($first);
        $this->assertSame('Extra', $first['data']['name']);
        $this->assertSame('publish', $first['data']['status']);
        $this->assertSame(0, $first['data']['sort_order']);

        $second = $this->routes->createModifier(['item_id' => $itemId, 'name' => 'Large', 'status' => 'draft']);
        $this->assertIsArray($second);
        $this->assertSame(1, $second['data']['sort_order']);
        $this->assertSame('draft', $second['data']['status']);

        $this->assertCount(2, $this->modifierEvents);
        $this->assertSame('created', $this->modifierEvents[0]['action']);
        $this->assertSame((int) $first['data']['id'], (int) $this->modifierEvents[0]['id']);
        $this->assertSame($menuId, (int) $this->modifierEvents[0]['menu_id']);
        $this->assertSame($itemId, (int) $this->modifierEvents[0]['item_id']);
        $this->assertCount(2, $this->menuEvents);
        $this->assertSame('modifier', $this->menuEvents[0]['reason']);
        $this->assertSame('created', $this->menuEvents[0]['action']);
        $this->assertSame($menuId, (int) $this->menuEvents[0]['id']);
    }

    public function test_create_validates_parent_name_and_price(): void
    {
        $menuId = $this->seedMenu();
        $itemId = $this->seedItem($menuId);

        $unknown = $this->routes->createModifier(['item_id' => 999, 'name' => 'Ghost']);
        $this->assertIsArray($unknown);
        $this->assertSame('smooth_menu_item_not_found', $unknown['code']);
        $this->assertSame(404, $unknown['data']['status']);

        $nameless = $this->routes->createModifier(['item_id' => $itemId, 'description' => 'No name']);
        $this->assertIsArray($nameless);
        $this->assertSame('smooth_modifier_missing_name', $nameless['code']);
        $this->assertSame(400, $nameless['data']['status']);

        $priceless = $this->routes->createModifier(['item_id' => $itemId, 'name' => 'Extra', 'price_cents' => -10]);
        $this->assertIsArray($priceless);
        $this->assertSame('smooth_modifier_invalid_price', $priceless['code']);

        $this->assertSame([], $this->modifiers->listByItem($itemId));
        $this->assertSame([], $this->modifiers->listByItem($itemId, 'draft'));
        $this->assertSame([], $this->modifierEvents);
    }

    public function test_update_touches_only_present_fields(): void
    {
        $menuId = $this->seedMenu();
        $itemId = $this->seedItem($menuId);
        $otherItem = $this->seedItem($menuId, ['name' => 'Salad', 'sort_order' => 1]);
        $modifierId = $this->seedModifier($itemId);

        $missing = $this->routes->updateModifier(['id' => 999, 'name' => 'Ghost']);
        $this->assertIsArray($missing);
        $this->assertSame('smooth_modifier_not_found', $missing['code']);

        $empty = $this->routes->updateModifier(['id' => $modifierId, 'name' => '  ']);
        $this->assertIsArray($empty);
        $this->assertSame('smooth_modifier_missing_name', $empty['code']);

        $badPrice = $this->routes->updateModifier(['id' => $modifierId, 'price_cents' => 'lots']);
        $this->assertIsArray($badPrice);
        $this->assertSame('smooth_modifier_invalid_price', $badPrice['code']);

        $updated = $this->routes->updateModifier([
            'id' => $modifierId,
            'name' => 'Double',
            'status' => 'draft',
            'item_id' => $otherItem,
        ]);
        $this->assertIsArray($updated);
        $this->assertSame('Double', $updated['data']['name']);
        $this->assertSame('draft', $updated['data']['status']);
        $this->assertSame(100, $updated['data']['price_cents']);
        $this->assertSame($itemId, (int) $updated['data']['item_id']);

        $this->assertCount(1, $this->modifierEvents);
        $this->assertSame('updated', $this->modifierEvents[0]['action']);
        $this->assertSame($modifierId, (int) $this->modifierEvents[0]['id']);
        $this->assertSame($menuId, (int) $this->modifierEvents[0]['menu_id']);
        $this->assertCount(1, $this->menuEvents);
        $this->assertSame('modifier', $this->menuEvents[0]['reason']);
    }

    public function test_delete_round_trip_and_unknown_id(): void
    {
        $menuId = $this->seedMenu();
        $itemId = $this->seedItem($menuId);
        $modifierId = $this->seedModifier($itemId);

        $missing = $this->routes->deleteModifier(['id' => 999]);
        $this->assertIsArray($missing);
        $this->assertSame('smooth_modifier_not_found', $missing['code']);

        $deleted = $this->routes->deleteModifier(['id' => $modifierId]);
        $this->assertIsArray($deleted);
        $this->assertSame(['deleted' => true, 'id' => $modifierId], $deleted['data']);
        $this->assertNull($this->modifiers->findById($modifierId));

        $this->assertCount(1, $this->modifierEvents);
        $this->assertSame('deleted', $this->modifierEvents[0]['action']);
        $this->assertSame($itemId, (int) $this->modifierEvents[0]['item_id']);
        $this->assertCount(1, $this->menuEvents);
        $this->assertSame('deleted', $this->menuEvents[0]['action']);
    }

    public function test_reorder_enforces_ownership_and_dense_order(): void
    {
        $menuId = $this->seedMenu();
        $itemId = $this->seedItem($menuId);
        $otherItem = $this->seedItem($menuId, ['name' => 'Salad', 'sort_order' => 1]);
        $first = $this->seedModifier($itemId, ['name' => 'Alpha', 'sort_order' => 0]);
        $second = $this->seedModifier($itemId, ['name' => 'Beta', 'sort_order' => 3]);
        $foreign = $this->seedModifier($otherItem, ['name' => 'Foreign', 'sort_order' => 0]);

        $unknown = $this->routes->reorderModifiers(['item_id' => 999, 'ids' => [$first]]);
        $this->assertIsArray($unknown);
        $this->assertSame('smooth_menu_item_not_found', $unknown['code']);

        $stolen = $this->routes->reorderModifiers(['item_id' => $itemId, 'ids' => [$first, $foreign]]);
        $this->assertIsArray($stolen);
        $this->assertSame('smooth_modifier_order_mismatch', $stolen['code']);
        $this->assertSame(400, $stolen['data']['status']);

        $orders = [];
        foreach ($this->modifiers->listByItem($itemId) as $row) {
            $orders[(int) $row['id']] = (int) $row['sort_order'];
        }
        $this->assertSame([0, 3], [$orders[$first], $orders[$second]]);
        $this->assertSame([], $this->modifierEvents);

        $reordered = $this->routes->reorderModifiers(['item_id' => $itemId, 'ids' => [$second, $first]]);
        $this->assertIsArray($reordered);
        $this->assertSame([$second, $first], $reordered['data']['ids']);
        $this->assertSame(['Beta', 'Alpha'], array_column($this->modifiers->listByItem($itemId), 'name'));

        $this->assertCount(1, $this->modifierEvents);
        $this->assertSame('reordered', $this->modifierEvents[0]['action']);
        $this->assertSame($itemId, (int) $this->modifierEvents[0]['item_id']);
        $this->assertSame($menuId, (int) $this->modifierEvents[0]['menu_id']);
        $this->assertCount(1, $this->menuEvents);
        $this->assertSame('modifier', $this->menuEvents[0]['reason']);
    }

    public function test_response_rows_match_the_declared_schema(): void
    {
        $menuId = $this->seedMenu();
        $itemId = $this->seedItem($menuId);
        $schema = $this->routes->modifierSchema();

        $response = $this->routes->createModifier(['item_id' => $itemId, 'name' => 'Extra']);
        $this->assertIsArray($response);

        $allowed = array_keys($schema['properties']);
        foreach (array_keys($response['data']) as $field) {
            $this->assertContains(
                $field,
                $allowed,
                sprintf('Response field "%s" is missing from modifierSchema().', $field)
            );
        }
        foreach ($schema['required'] as $field) {
            $this->assertArrayHasKey($field, $response['data']);
        }
    }

    public function test_saved_event_name_is_namespaced(): void
    {
        $this->assertSame('smooth_restaurant_modifier_saved', DomainEvents::MODIFIER_SAVED);
    }
}
