<?php

/**
 * Menu item REST routes.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Menu;

use SmoothRestaurant\Contracts\MenuItemRepositoryInterface;
use SmoothRestaurant\Contracts\MenuRepositoryInterface;
use SmoothRestaurant\Contracts\ModifierRepositoryInterface;
use SmoothRestaurant\Domains\Shared\DomainEvents;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\RestProvider;

/**
 * Class MenuItemRoutes
 *
 * Registers the capability-gated item writes: create under a menu, partial
 * update, delete with modifier cascade, and bulk reorder. Constructed from
 * container-bound repositories — raw database access stays inside
 * src/Database/. Callbacks accept the WP_REST_Request in production and
 * plain param arrays in unit tests, and return plain arrays (WordPress
 * serializes them to JSON); errors carry a machine-readable code plus
 * message, with the HTTP status applied through WP_REST_Response whenever
 * the class is available.
 */
final class MenuItemRoutes
{
    use MenuRestSupport;

    /**
     * Constructor.
     *
     * @param MenuRepositoryInterface     $menus     Menus table repository.
     * @param MenuItemRepositoryInterface $items     Menu items table repository.
     * @param ModifierRepositoryInterface $modifiers Modifiers table repository.
     */
    public function __construct(
        private MenuRepositoryInterface $menus,
        private MenuItemRepositoryInterface $items,
        private ModifierRepositoryInterface $modifiers
    ) {
    }

    /**
     * Register the item routes. No-op without WordPress.
     *
     * @return void
     */
    public function register(): void
    {
        if (! function_exists('register_rest_route')) {
            return;
        }

        $manage = RestProvider::capability(MenuProvider::MANAGE_CAP);

        // Endpoint lists mix numeric entries with the 'schema' key — the
        // shape register_rest_route() documents; the WP stub types $args as
        // array<string, mixed>, hence the local annotation.
        /** @var array<string, mixed> $collectionArgs */
        $collectionArgs = [
            [
                'methods' => 'POST',
                'callback' => [$this, 'createItem'],
                'permission_callback' => $manage,
                'args' => \array_merge(
                    ['menu_id' => ['type' => 'integer', 'required' => true, 'description' => 'Parent menu row id.']],
                    $this->writeArgs()
                ),
            ],
            'schema' => [$this, 'itemSchema'],
        ];
        register_rest_route(RestProvider::NAMESPACE, '/menus/(?P<menu_id>\d+)/items', $collectionArgs);

        /** @var array<string, mixed> $singleArgs */
        $singleArgs = [
            [
                'methods' => 'PATCH',
                'callback' => [$this, 'updateItem'],
                'permission_callback' => $manage,
                'args' => $this->writeArgs(),
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'deleteItem'],
                'permission_callback' => $manage,
                'args' => $this->idArgs(),
            ],
            'schema' => [$this, 'itemSchema'],
        ];
        register_rest_route(RestProvider::NAMESPACE, '/items/(?P<id>\d+)', $singleArgs);

        /** @var array<string, mixed> $orderArgs */
        $orderArgs = [
            [
                'methods' => 'PUT',
                'callback' => [$this, 'reorderItems'],
                'permission_callback' => $manage,
                'args' => \array_merge(
                    ['menu_id' => ['type' => 'integer', 'required' => true, 'description' => 'Parent menu row id.']],
                    $this->reorderArgs()
                ),
            ],
        ];
        register_rest_route(RestProvider::NAMESPACE, '/menus/(?P<menu_id>\d+)/items/order', $orderArgs);
    }

    /**
     * Create an item row under a menu (capability-gated).
     *
     * Omitted sort_order appends at the end of the menu.
     *
     * @param mixed $request WP_REST_Request in production, param array in unit tests.
     * @return mixed Response array, or WP_REST_Response when available.
     */
    public function createItem(mixed $request): mixed
    {
        $params = self::params($request);
        $menuId = isset($params['menu_id']) ? (int) $params['menu_id'] : 0;

        $menu = 0 !== $menuId ? $this->menus->findById($menuId) : null;
        if (null === $menu) {
            return $this->error('smooth_menu_not_found', 'Menu not found.', 404);
        }

        $name = \trim((string) ($params['name'] ?? ''));
        if ('' === $name) {
            return $this->error('smooth_menu_item_missing_name', 'The name field is required.', 400);
        }

        $price = $this->nonNegativeInt($params, 'price_cents');
        if (! $price['valid']) {
            return $this->error(
                'smooth_menu_item_invalid_price',
                'The price_cents field must be an integer >= 0.',
                400
            );
        }

        $image = $this->nonNegativeInt($params, 'image_id');
        if (! $image['valid']) {
            return $this->error(
                'smooth_menu_item_invalid_image',
                'The image_id field must be an integer >= 0.',
                400
            );
        }

        $menuIdInt = (int) $menu['id'];
        $max = $this->items->maxSortOrderForMenu($menuIdInt);
        $id = $this->items->insert([
            'menu_id' => $menuIdInt,
            'name' => $name,
            'description' => (string) ($params['description'] ?? ''),
            'price_cents' => $price['value'],
            'image_id' => $image['value'],
            'status' => $this->status($params),
            'sort_order' => isset($params['sort_order']) ? (int) $params['sort_order'] : (null === $max ? 0 : $max + 1),
        ]);

        $item = $this->items->findById($id);
        $item = \is_array($item) ? $item : ['id' => $id, 'menu_id' => $menuIdInt, 'name' => $name];
        DomainEvents::dispatch(
            DomainEvents::MENU_ITEM_SAVED,
            ['action' => 'created', 'id' => $id, 'menu_id' => $menuIdInt, 'row' => $item]
        );
        DomainEvents::dispatch(
            DomainEvents::MENU_SAVED,
            ['id' => $menuIdInt, 'reason' => 'item', 'action' => 'created']
        );

        return $this->respond(['data' => $item], 201);
    }

    /**
     * Update an item row (capability-gated).
     *
     * Touches only present fields; menu_id is immutable and silently ignored.
     *
     * @param mixed $request WP_REST_Request in production, param array in unit tests.
     * @return mixed Response array, or WP_REST_Response when available.
     */
    public function updateItem(mixed $request): mixed
    {
        $params = self::params($request);
        $id = isset($params['id']) ? (int) $params['id'] : 0;

        $item = 0 !== $id ? $this->items->findById($id) : null;
        if (null === $item) {
            return $this->error('smooth_menu_item_not_found', 'Menu item not found.', 404);
        }

        $data = [];
        if (isset($params['name'])) {
            $name = \trim((string) $params['name']);
            if ('' === $name) {
                return $this->error('smooth_menu_item_missing_name', 'The name field must not be empty.', 400);
            }
            $data['name'] = $name;
        }
        if (isset($params['description'])) {
            $data['description'] = (string) $params['description'];
        }
        if (isset($params['price_cents'])) {
            $price = $this->nonNegativeInt($params, 'price_cents');
            if (! $price['valid']) {
                return $this->error(
                    'smooth_menu_item_invalid_price',
                    'The price_cents field must be an integer >= 0.',
                    400
                );
            }
            $data['price_cents'] = $price['value'];
        }
        if (isset($params['image_id'])) {
            $image = $this->nonNegativeInt($params, 'image_id');
            if (! $image['valid']) {
                return $this->error(
                    'smooth_menu_item_invalid_image',
                    'The image_id field must be an integer >= 0.',
                    400
                );
            }
            $data['image_id'] = $image['value'];
        }
        if (isset($params['status'])) {
            $data['status'] = $this->status($params);
        }
        if (isset($params['sort_order'])) {
            $data['sort_order'] = (int) $params['sort_order'];
        }

        $itemId = (int) $item['id'];
        if ([] !== $data) {
            $this->items->update($itemId, $data);
        }

        $updated = $this->items->findById($itemId);
        $updated = \is_array($updated) ? $updated : $item;
        $menuId = (int) ($item['menu_id'] ?? 0);
        DomainEvents::dispatch(
            DomainEvents::MENU_ITEM_SAVED,
            ['action' => 'updated', 'id' => $itemId, 'menu_id' => $menuId, 'row' => $updated]
        );
        DomainEvents::dispatch(
            DomainEvents::MENU_SAVED,
            ['id' => $menuId, 'reason' => 'item', 'action' => 'updated']
        );

        return ['data' => $updated];
    }

    /**
     * Delete an item row with its modifiers (capability-gated).
     *
     * The cascade covers both publish and draft modifiers.
     *
     * @param mixed $request WP_REST_Request in production, param array in unit tests.
     * @return mixed Response array, or WP_REST_Response when available.
     */
    public function deleteItem(mixed $request): mixed
    {
        $params = self::params($request);
        $id = isset($params['id']) ? (int) $params['id'] : 0;

        $item = 0 !== $id ? $this->items->findById($id) : null;
        if (null === $item) {
            return $this->error('smooth_menu_item_not_found', 'Menu item not found.', 404);
        }

        $itemId = (int) $item['id'];
        $menuId = (int) ($item['menu_id'] ?? 0);
        foreach (['publish', 'draft'] as $status) {
            foreach ($this->modifiers->listByItem($itemId, $status) as $modifier) {
                $this->modifiers->delete((int) ($modifier['id'] ?? 0));
            }
        }
        $this->items->delete($itemId);

        DomainEvents::dispatch(
            DomainEvents::MENU_ITEM_SAVED,
            ['action' => 'deleted', 'id' => $itemId, 'menu_id' => $menuId]
        );
        DomainEvents::dispatch(
            DomainEvents::MENU_SAVED,
            ['id' => $menuId, 'reason' => 'item', 'action' => 'deleted']
        );

        return ['data' => ['deleted' => true, 'id' => $itemId]];
    }

    /**
     * Reorder a menu's items (capability-gated).
     *
     * Accepts the menu's full ordered id set and assigns dense sort_order
     * (0..n-1) server-side. Stale sets are rejected before any write.
     *
     * @param mixed $request WP_REST_Request in production, param array in unit tests.
     * @return mixed Response array, or WP_REST_Response when available.
     */
    public function reorderItems(mixed $request): mixed
    {
        $params = self::params($request);
        $menuId = isset($params['menu_id']) ? (int) $params['menu_id'] : 0;

        $menu = 0 !== $menuId ? $this->menus->findById($menuId) : null;
        if (null === $menu) {
            return $this->error('smooth_menu_not_found', 'Menu not found.', 404);
        }

        $ids = $this->orderIds($params);
        if (null === $ids) {
            return $this->error(
                'smooth_menu_item_order_mismatch',
                'The ids field must be the menu\'s full ordered item id set.',
                400
            );
        }

        $menuIdInt = (int) $menu['id'];
        $current = [];
        foreach (['publish', 'draft'] as $status) {
            foreach ($this->items->listByMenu($menuIdInt, $status) as $item) {
                $current[] = (int) ($item['id'] ?? 0);
            }
        }
        if (! $this->sameIdSet($ids, $current)) {
            return $this->error(
                'smooth_menu_item_order_mismatch',
                'The ids field must match the menu\'s full current item set.',
                400
            );
        }

        foreach ($ids as $index => $itemId) {
            $this->items->update($itemId, ['sort_order' => $index]);
        }

        DomainEvents::dispatch(
            DomainEvents::MENU_ITEM_SAVED,
            ['action' => 'reordered', 'id' => $menuIdInt, 'menu_id' => $menuIdInt, 'ids' => $ids]
        );
        DomainEvents::dispatch(
            DomainEvents::MENU_SAVED,
            ['id' => $menuIdInt, 'reason' => 'item', 'action' => 'reordered']
        );

        return ['data' => ['ids' => $ids]];
    }

    /**
     * JSON-Schema for a single item object.
     *
     * @return array<string, mixed>
     */
    public function itemSchema(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'menu-item',
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Item row id.'],
                'menu_id' => ['type' => 'integer', 'description' => 'Parent menu row id (immutable).'],
                'name' => ['type' => 'string', 'description' => 'Display name.'],
                'description' => ['type' => 'string', 'description' => 'Long description.'],
                'price_cents' => ['type' => 'integer', 'description' => 'Price in cents (>= 0).'],
                'image_id' => ['type' => 'integer', 'description' => 'WP media attachment id (>= 0).'],
                'status' => ['type' => 'string', 'enum' => ['publish', 'draft'], 'description' => 'Row status.'],
                'sort_order' => ['type' => 'integer', 'description' => 'Display order.'],
                'created_at' => ['type' => 'string', 'format' => 'date-time', 'description' => 'Creation time.'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time', 'description' => 'Last update time.'],
            ],
            'required' => ['id', 'menu_id', 'name', 'status'],
        ];
    }

    /**
     * Request args for the item writes.
     *
     * @return array<string, mixed>
     */
    private function writeArgs(): array
    {
        return [
            'name' => ['type' => 'string', 'description' => 'Display name (required on create).'],
            'description' => ['type' => 'string', 'description' => 'Long description.'],
            'price_cents' => ['type' => 'integer', 'minimum' => 0, 'description' => 'Price in cents.'],
            'image_id' => ['type' => 'integer', 'minimum' => 0, 'description' => 'WP media attachment id.'],
            'status' => ['type' => 'string', 'enum' => ['publish', 'draft'], 'description' => 'Row status.'],
            'sort_order' => ['type' => 'integer', 'description' => 'Display order.'],
        ];
    }

    /**
     * Request args for routes carrying an id placeholder.
     *
     * @return array<string, mixed>
     */
    private function idArgs(): array
    {
        return [
            'id' => ['type' => 'integer', 'required' => true, 'description' => 'Item row id.'],
        ];
    }

    /**
     * Request args for the bulk reorder endpoint.
     *
     * @return array<string, mixed>
     */
    private function reorderArgs(): array
    {
        return [
            'ids' => [
                'type' => 'array',
                'items' => ['type' => 'integer'],
                'required' => true,
                'description' => 'Full ordered item id set for the menu.',
            ],
        ];
    }
}
