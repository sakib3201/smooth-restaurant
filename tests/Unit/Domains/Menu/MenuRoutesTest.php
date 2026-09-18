<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Domains\Menu;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\Repositories\MenuItemRepository;
use SmoothRestaurant\Database\Repositories\MenuRepository;
use SmoothRestaurant\Database\Repositories\ModifierRepository;
use SmoothRestaurant\Domains\Menu\MenuRoutes;
use SmoothRestaurant\Domains\Menu\MenuService;
use SmoothRestaurant\Domains\Shared\DomainEvents;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Tests\Unit\Database\Support\FakeWpdb;

/**
 * Unit tests for the menu REST controller.
 *
 * Callbacks run against plain param arrays (production passes the
 * WP_REST_Request, which the controller reads through get_params()).
 * Status codes and the Cache-Control header are production edges applied
 * through WP_REST_Response; unit context asserts the payload arrays, and
 * the Bruno CI contracts assert the live codes and headers.
 */
final class MenuRoutesTest extends TestCase
{
    private FakeWpdb $db;

    private MenuRoutes $routes;

    private MenuRepository $menus;

    /**
     * Payloads captured from the smooth.menu.saved event.
     *
     * @var list<array<string, mixed>>
     */
    private array $savedEvents = [];

    protected function setUp(): void
    {
        parent::setUp();
        sr_test_reset_stubs();
        $this->db = new FakeWpdb();
        $this->menus = new MenuRepository($this->db);
        $this->routes = new MenuRoutes(
            $this->menus,
            new MenuItemRepository($this->db),
            new ModifierRepository($this->db)
        );
        add_action('smooth.menu.saved', function (array $payload): void {
            $this->savedEvents[] = $payload;
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

    public function test_registers_collection_and_single_routes(): void
    {
        $routes = $this->registeredRoutes();

        $this->assertArrayHasKey('/menus', $routes);
        $this->assertArrayHasKey('/menus/(?P<id>\d+)', $routes);
        $this->assertSame('smooth/v1', $routes['/menus']['namespace']);
    }

    public function test_public_reads_need_no_capability(): void
    {
        $routes = $this->registeredRoutes();

        $collection = $routes['/menus']['args'][0];
        $single = $routes['/menus/(?P<id>\d+)']['args'][0];

        $this->assertSame('GET', $collection['methods']);
        $this->assertSame('__return_true', $collection['permission_callback']);
        $this->assertSame('GET', $single['methods']);
        $this->assertSame('__return_true', $single['permission_callback']);
    }

    public function test_writes_require_the_manage_capability(): void
    {
        $routes = $this->registeredRoutes();

        $endpoints = array_merge($routes['/menus']['args'], $routes['/menus/(?P<id>\d+)']['args']);
        $writeCallbacks = [];
        foreach ($endpoints as $endpoint) {
            if (! is_array($endpoint) || ! isset($endpoint['methods'])) {
                continue;
            }
            if (in_array($endpoint['methods'], ['POST', ['PUT', 'PATCH'], 'DELETE'], true)) {
                $writeCallbacks[] = $endpoint['permission_callback'];
            }
        }

        $this->assertCount(3, $writeCallbacks);
        foreach ($writeCallbacks as $callback) {
            $this->assertIsCallable($callback);
            $this->assertFalse($callback());
        }

        sr_test_grant_caps([MenuProvider::MANAGE_CAP]);
        foreach ($writeCallbacks as $callback) {
            $this->assertTrue($callback());
        }
    }

    public function test_list_returns_paginated_collection(): void
    {
        $this->seedMenu(['name' => 'Beta', 'sort_order' => 1]);
        $this->seedMenu(['name' => 'Alpha', 'sort_order' => 0]);
        $this->seedMenu(['name' => 'Draft', 'status' => 'draft']);

        $response = $this->routes->listMenus(['page' => 1, 'per_page' => 10]);
        $this->assertIsArray($response);
        $this->assertSame(['Alpha', 'Beta'], array_column($response['data'], 'name'));
        $this->assertSame(['page' => 1, 'per_page' => 10], $response['meta']);

        $second = $this->routes->listMenus(['page' => 2, 'per_page' => 1]);
        $this->assertIsArray($second);
        $this->assertSame(['Beta'], array_column($second['data'], 'name'));

        $searched = $this->routes->listMenus(['search' => 'alp']);
        $this->assertIsArray($searched);
        $this->assertSame(['Alpha'], array_column($searched['data'], 'name'));
    }

    public function test_get_returns_tree_and_404s_unknown_or_draft(): void
    {
        $menuId = $this->seedMenu();
        $items = new MenuItemRepository($this->db);
        $modifiers = new ModifierRepository($this->db);
        $itemId = $items->insert([
            'menu_id' => $menuId, 'name' => 'Soup', 'description' => '', 'price_cents' => 950,
            'image_id' => 0, 'status' => 'publish', 'sort_order' => 0,
        ]);
        $modifiers->insert(['item_id' => $itemId, 'name' => 'Large', 'price_cents' => 200, 'sort_order' => 0]);
        $draftId = $this->seedMenu(['name' => 'Draft', 'status' => 'draft']);

        $response = $this->routes->getMenu(['id' => $menuId]);
        $this->assertIsArray($response);
        $this->assertSame('Lunch', $response['data']['menu']['name']);
        $this->assertSame('Soup', $response['data']['items'][0]['item']['name']);
        $this->assertSame('Large', $response['data']['items'][0]['modifiers'][0]['name']);

        $missing = $this->routes->getMenu(['id' => 999]);
        $this->assertIsArray($missing);
        $this->assertSame('smooth_menu_not_found', $missing['code']);
        $this->assertSame(404, $missing['data']['status']);

        $draft = $this->routes->getMenu(['id' => $draftId]);
        $this->assertIsArray($draft);
        $this->assertSame('smooth_menu_not_found', $draft['code']);
    }

    public function test_create_validates_and_deduplicates_slug(): void
    {
        $rejected = $this->routes->createMenu(['description' => 'No name']);
        $this->assertIsArray($rejected);
        $this->assertSame('smooth_menu_missing_name', $rejected['code']);
        $this->assertSame(400, $rejected['data']['status']);

        $first = $this->routes->createMenu(['name' => 'Brunch']);
        $this->assertIsArray($first);
        $this->assertSame('brunch', $first['data']['slug']);

        $second = $this->routes->createMenu(['name' => 'Brunch']);
        $this->assertIsArray($second);
        $this->assertSame('brunch-2', $second['data']['slug']);

        $this->assertCount(2, $this->savedEvents);
        $this->assertSame((int) $first['data']['id'], (int) $this->savedEvents[0]['id']);
    }

    public function test_update_and_delete_round_trip_with_cascade(): void
    {
        $menuId = $this->seedMenu();
        $items = new MenuItemRepository($this->db);
        $modifiers = new ModifierRepository($this->db);
        $itemId = $items->insert([
            'menu_id' => $menuId, 'name' => 'Soup', 'description' => '', 'price_cents' => 950,
            'image_id' => 0, 'status' => 'publish', 'sort_order' => 0,
        ]);
        $modifiers->insert(['item_id' => $itemId, 'name' => 'Large', 'price_cents' => 200, 'sort_order' => 0]);

        $missing = $this->routes->updateMenu(['id' => 999, 'name' => 'Ghost']);
        $this->assertIsArray($missing);
        $this->assertSame('smooth_menu_not_found', $missing['code']);

        $empty = $this->routes->updateMenu(['id' => $menuId, 'name' => '  ']);
        $this->assertIsArray($empty);
        $this->assertSame('smooth_menu_missing_name', $empty['code']);

        $updated = $this->routes->updateMenu(['id' => $menuId, 'name' => 'Dinner', 'status' => 'draft']);
        $this->assertIsArray($updated);
        $this->assertSame('Dinner', $updated['data']['name']);
        $this->assertSame('dinner', $updated['data']['slug']);
        $this->assertSame('draft', $updated['data']['status']);
        $this->assertCount(1, $this->savedEvents);

        $deleted = $this->routes->deleteMenu(['id' => $menuId]);
        $this->assertIsArray($deleted);
        $this->assertSame(['deleted' => true, 'id' => $menuId], $deleted['data']);
        $this->assertNull($this->menus->findById($menuId));
        $this->assertSame([], $items->listByMenu($menuId, 'publish'));
        $this->assertSame([], $items->listByMenu($menuId, 'draft'));
        $this->assertSame([], $modifiers->listByItem($itemId));

        $gone = $this->routes->deleteMenu(['id' => $menuId]);
        $this->assertIsArray($gone);
        $this->assertSame('smooth_menu_not_found', $gone['code']);
    }

    public function test_response_rows_match_the_declared_schema(): void
    {
        $this->seedMenu();
        $schema = $this->routes->menuSchema();

        $response = $this->routes->listMenus([]);
        $this->assertIsArray($response);
        $this->assertNotEmpty($response['data']);

        $allowed = array_keys($schema['properties']);
        foreach ($response['data'] as $row) {
            foreach (array_keys($row) as $field) {
                $this->assertContains(
                    $field,
                    $allowed,
                    sprintf('Response field "%s" is missing from menuSchema().', $field)
                );
            }
            foreach ($schema['required'] as $field) {
                $this->assertArrayHasKey($field, $row);
            }
        }

        $collection = $this->routes->collectionSchema();
        $this->assertSame(['data', 'meta'], $collection['required']);
    }

    public function test_saved_event_name_is_namespaced(): void
    {
        $this->assertSame('smooth.menu.saved', DomainEvents::MENU_SAVED);
    }
}
