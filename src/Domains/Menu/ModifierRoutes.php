<?php

/**
 * Modifier REST routes.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Menu;

use SmoothRestaurant\Contracts\MenuItemRepositoryInterface;
use SmoothRestaurant\Contracts\ModifierRepositoryInterface;
use SmoothRestaurant\Domains\Shared\DomainEvents;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\RestProvider;

/**
 * Class ModifierRoutes
 *
 * Registers the capability-gated modifier writes: create under an item,
 * partial update, delete, and bulk reorder. Constructed from container-bound
 * repositories — raw database access stays inside src/Database/. Callbacks
 * accept the WP_REST_Request in production and plain param arrays in unit
 * tests, and return plain arrays (WordPress serializes them to JSON); errors
 * carry a machine-readable code plus message, with the HTTP status applied
 * through WP_REST_Response whenever the class is available.
 */
final class ModifierRoutes
{
    use MenuRestSupport;

    /**
     * Constructor.
     *
     * @param MenuItemRepositoryInterface $items     Menu items table repository.
     * @param ModifierRepositoryInterface $modifiers Modifiers table repository.
     */
    public function __construct(
        private MenuItemRepositoryInterface $items,
        private ModifierRepositoryInterface $modifiers
    ) {
    }

    /**
     * Register the modifier routes. No-op without WordPress.
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
                'callback' => [$this, 'createModifier'],
                'permission_callback' => $manage,
                'args' => \array_merge(
                    ['item_id' => ['type' => 'integer', 'required' => true, 'description' => 'Parent item row id.']],
                    $this->writeArgs()
                ),
            ],
            'schema' => [$this, 'modifierSchema'],
        ];
        register_rest_route(RestProvider::NAMESPACE, '/items/(?P<item_id>\d+)/modifiers', $collectionArgs);

        /** @var array<string, mixed> $singleArgs */
        $singleArgs = [
            [
                'methods' => 'PATCH',
                'callback' => [$this, 'updateModifier'],
                'permission_callback' => $manage,
                'args' => $this->writeArgs(),
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'deleteModifier'],
                'permission_callback' => $manage,
                'args' => $this->idArgs(),
            ],
            'schema' => [$this, 'modifierSchema'],
        ];
        register_rest_route(RestProvider::NAMESPACE, '/modifiers/(?P<id>\d+)', $singleArgs);

        /** @var array<string, mixed> $orderArgs */
        $orderArgs = [
            [
                'methods' => 'PUT',
                'callback' => [$this, 'reorderModifiers'],
                'permission_callback' => $manage,
                'args' => \array_merge(
                    ['item_id' => ['type' => 'integer', 'required' => true, 'description' => 'Parent item row id.']],
                    $this->reorderArgs()
                ),
            ],
        ];
        register_rest_route(RestProvider::NAMESPACE, '/items/(?P<item_id>\d+)/modifiers/order', $orderArgs);
    }

    /**
     * Create a modifier row under an item (capability-gated).
     *
     * Omitted sort_order appends at the end of the item.
     *
     * @param mixed $request WP_REST_Request in production, param array in unit tests.
     * @return mixed Response array, or WP_REST_Response when available.
     */
    public function createModifier(mixed $request): mixed
    {
        $params = self::params($request);
        $itemId = isset($params['item_id']) ? (int) $params['item_id'] : 0;

        $item = 0 !== $itemId ? $this->items->findById($itemId) : null;
        if (null === $item) {
            return $this->error('smooth_menu_item_not_found', 'Menu item not found.', 404);
        }

        $name = \trim((string) ($params['name'] ?? ''));
        if ('' === $name) {
            return $this->error('smooth_modifier_missing_name', 'The name field is required.', 400);
        }

        $price = $this->nonNegativeInt($params, 'price_cents');
        if (! $price['valid']) {
            return $this->error(
                'smooth_modifier_invalid_price',
                'The price_cents field must be an integer >= 0.',
                400
            );
        }

        $itemIdInt = (int) $item['id'];
        $menuId = (int) ($item['menu_id'] ?? 0);
        $max = $this->modifiers->maxSortOrderForItem($itemIdInt);
        $id = $this->modifiers->insert([
            'item_id' => $itemIdInt,
            'name' => $name,
            'price_cents' => $price['value'],
            'status' => $this->status($params),
            'sort_order' => isset($params['sort_order']) ? (int) $params['sort_order'] : (null === $max ? 0 : $max + 1),
        ]);

        $modifier = $this->modifiers->findById($id);
        $modifier = \is_array($modifier) ? $modifier : ['id' => $id, 'item_id' => $itemIdInt, 'name' => $name];
        DomainEvents::dispatch(
            DomainEvents::MODIFIER_SAVED,
            ['action' => 'created', 'id' => $id, 'menu_id' => $menuId, 'item_id' => $itemIdInt, 'row' => $modifier]
        );
        DomainEvents::dispatch(
            DomainEvents::MENU_SAVED,
            ['id' => $menuId, 'reason' => 'modifier', 'action' => 'created']
        );

        return $this->respond(['data' => $modifier], 201);
    }

    /**
     * Update a modifier row (capability-gated).
     *
     * Touches only present fields; item_id is immutable and silently ignored.
     *
     * @param mixed $request WP_REST_Request in production, param array in unit tests.
     * @return mixed Response array, or WP_REST_Response when available.
     */
    public function updateModifier(mixed $request): mixed
    {
        $params = self::params($request);
        $id = isset($params['id']) ? (int) $params['id'] : 0;

        $modifier = 0 !== $id ? $this->modifiers->findById($id) : null;
        if (null === $modifier) {
            return $this->error('smooth_modifier_not_found', 'Modifier not found.', 404);
        }

        $data = [];
        if (isset($params['name'])) {
            $name = \trim((string) $params['name']);
            if ('' === $name) {
                return $this->error('smooth_modifier_missing_name', 'The name field must not be empty.', 400);
            }
            $data['name'] = $name;
        }
        if (isset($params['price_cents'])) {
            $price = $this->nonNegativeInt($params, 'price_cents');
            if (! $price['valid']) {
                return $this->error(
                    'smooth_modifier_invalid_price',
                    'The price_cents field must be an integer >= 0.',
                    400
                );
            }
            $data['price_cents'] = $price['value'];
        }
        if (isset($params['status'])) {
            $data['status'] = $this->status($params);
        }
        if (isset($params['sort_order'])) {
            $data['sort_order'] = (int) $params['sort_order'];
        }

        $modifierId = (int) $modifier['id'];
        if ([] !== $data) {
            $this->modifiers->update($modifierId, $data);
        }

        $updated = $this->modifiers->findById($modifierId);
        $updated = \is_array($updated) ? $updated : $modifier;
        $itemId = (int) ($modifier['item_id'] ?? 0);
        $item = 0 !== $itemId ? $this->items->findById($itemId) : null;
        $menuId = null !== $item ? (int) ($item['menu_id'] ?? 0) : 0;
        DomainEvents::dispatch(
            DomainEvents::MODIFIER_SAVED,
            ['action' => 'updated', 'id' => $modifierId, 'menu_id' => $menuId, 'item_id' => $itemId, 'row' => $updated]
        );
        DomainEvents::dispatch(
            DomainEvents::MENU_SAVED,
            ['id' => $menuId, 'reason' => 'modifier', 'action' => 'updated']
        );

        return ['data' => $updated];
    }

    /**
     * Delete a modifier row (capability-gated).
     *
     * @param mixed $request WP_REST_Request in production, param array in unit tests.
     * @return mixed Response array, or WP_REST_Response when available.
     */
    public function deleteModifier(mixed $request): mixed
    {
        $params = self::params($request);
        $id = isset($params['id']) ? (int) $params['id'] : 0;

        $modifier = 0 !== $id ? $this->modifiers->findById($id) : null;
        if (null === $modifier) {
            return $this->error('smooth_modifier_not_found', 'Modifier not found.', 404);
        }

        $modifierId = (int) $modifier['id'];
        $itemId = (int) ($modifier['item_id'] ?? 0);
        $item = 0 !== $itemId ? $this->items->findById($itemId) : null;
        $menuId = null !== $item ? (int) ($item['menu_id'] ?? 0) : 0;
        $this->modifiers->delete($modifierId);

        DomainEvents::dispatch(
            DomainEvents::MODIFIER_SAVED,
            ['action' => 'deleted', 'id' => $modifierId, 'menu_id' => $menuId, 'item_id' => $itemId]
        );
        DomainEvents::dispatch(
            DomainEvents::MENU_SAVED,
            ['id' => $menuId, 'reason' => 'modifier', 'action' => 'deleted']
        );

        return ['data' => ['deleted' => true, 'id' => $modifierId]];
    }

    /**
     * Reorder an item's modifiers (capability-gated).
     *
     * Accepts the item's full ordered id set and assigns dense sort_order
     * (0..n-1) server-side. Stale sets are rejected before any write.
     *
     * @param mixed $request WP_REST_Request in production, param array in unit tests.
     * @return mixed Response array, or WP_REST_Response when available.
     */
    public function reorderModifiers(mixed $request): mixed
    {
        $params = self::params($request);
        $itemId = isset($params['item_id']) ? (int) $params['item_id'] : 0;

        $item = 0 !== $itemId ? $this->items->findById($itemId) : null;
        if (null === $item) {
            return $this->error('smooth_menu_item_not_found', 'Menu item not found.', 404);
        }

        $ids = $this->orderIds($params);
        if (null === $ids) {
            return $this->error(
                'smooth_modifier_order_mismatch',
                'The ids field must be the item\'s full ordered modifier id set.',
                400
            );
        }

        $itemIdInt = (int) $item['id'];
        $menuId = (int) ($item['menu_id'] ?? 0);
        $current = [];
        foreach (['publish', 'draft'] as $status) {
            foreach ($this->modifiers->listByItem($itemIdInt, $status) as $modifier) {
                $current[] = (int) ($modifier['id'] ?? 0);
            }
        }
        if (! $this->sameIdSet($ids, $current)) {
            return $this->error(
                'smooth_modifier_order_mismatch',
                'The ids field must match the item\'s full current modifier set.',
                400
            );
        }

        foreach ($ids as $index => $modifierId) {
            $this->modifiers->update($modifierId, ['sort_order' => $index]);
        }

        DomainEvents::dispatch(
            DomainEvents::MODIFIER_SAVED,
            ['action' => 'reordered', 'id' => $itemIdInt, 'menu_id' => $menuId, 'item_id' => $itemIdInt, 'ids' => $ids]
        );
        DomainEvents::dispatch(
            DomainEvents::MENU_SAVED,
            ['id' => $menuId, 'reason' => 'modifier', 'action' => 'reordered']
        );

        return ['data' => ['ids' => $ids]];
    }

    /**
     * JSON-Schema for a single modifier object.
     *
     * @return array<string, mixed>
     */
    public function modifierSchema(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'modifier',
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Modifier row id.'],
                'item_id' => ['type' => 'integer', 'description' => 'Parent item row id (immutable).'],
                'name' => ['type' => 'string', 'description' => 'Display name.'],
                'price_cents' => ['type' => 'integer', 'description' => 'Price delta in cents (>= 0).'],
                'status' => ['type' => 'string', 'enum' => ['publish', 'draft'], 'description' => 'Row status.'],
                'sort_order' => ['type' => 'integer', 'description' => 'Display order.'],
                'created_at' => ['type' => 'string', 'format' => 'date-time', 'description' => 'Creation time.'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time', 'description' => 'Last update time.'],
            ],
            'required' => ['id', 'item_id', 'name', 'status'],
        ];
    }

    /**
     * Request args for the modifier writes.
     *
     * @return array<string, mixed>
     */
    private function writeArgs(): array
    {
        return [
            'name' => ['type' => 'string', 'description' => 'Display name (required on create).'],
            'price_cents' => ['type' => 'integer', 'minimum' => 0, 'description' => 'Price delta in cents.'],
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
            'id' => ['type' => 'integer', 'required' => true, 'description' => 'Modifier row id.'],
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
                'description' => 'Full ordered modifier id set for the item.',
            ],
        ];
    }
}
