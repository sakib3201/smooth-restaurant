<?php

/**
 * Blocks service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Contracts\MenuItemRepositoryInterface;
use SmoothRestaurant\Contracts\MenuRepositoryInterface;
use SmoothRestaurant\Contracts\ModifierRepositoryInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Domains\Menu\MenuService;

/**
 * Class BlocksProvider
 *
 * Hooks block registration in boot() on diner frontend (view) requests
 * and wp-admin (block editor); cron and REST never render blocks. Owns the
 * read-only smooth/menu bindings source (live custom-table reads) so the
 * editor seam lives with the editor-aware provider; MenuProvider stays
 * frontend.
 */
final class BlocksProvider extends ServiceProvider
{
    /**
     * Bindings source name for live menu data.
     */
    public const BINDINGS_SOURCE = 'smooth/menu';

    /**
     * Request contexts this provider participates in.
     *
     * Frontend views plus wp-admin (block editor); cron and REST never
     * render blocks.
     *
     * @return list<string>
     */
    public static function contexts(): array
    {
        return array( 'frontend', 'admin' );
    }

    /**
     * Register services with the container.
     *
     * Bind-only: no hooks, no database access, no translation calls.
     * The bindings source needs no bindings of its own; repositories resolve
     * from the owning MenuProvider at render time.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function register(Container $container): void
    {
    }

    /**
     * Boot the provider after all providers are registered.
     *
     * @param Container $container The DI container.
     * @return void
     */
    public function boot(Container $container): void
    {
        if ($this->isBackendRequest()) {
            return;
        }

        add_action('init', array( $this, 'registerBlocks' ));
        $this->markBooted();
    }

    /**
     * Register blocks and the menu bindings source.
     *
     * The smooth/menu source is read-only and reads live custom-table rows
     * on every render, so reorder, inline edits, and autosave can never
     * corrupt menu output. Revision restores do NOT roll back table data
     * (the revision restores markup + context ids; names/prices keep showing
     * the current table state) — see docs/menu-binding-contract.md. Editor
     * writes travel through the cap-gated management REST routes.
     *
     * @return void
     */
    public function registerBlocks(): void
    {
        if (! function_exists('register_block_bindings_source')) {
            return;
        }

        register_block_bindings_source(
            self::BINDINGS_SOURCE,
            array(
                'label' => 'Smooth menu data',
                'get_value_callback' => array( $this, 'getBindingValue' ),
                'use_context' => array( 'smooth/menuId', 'smooth/itemId' ),
            )
        );
    }

    /**
     * Resolve a binding value from the live menu tables.
     *
     * Returns null for unknown keys and missing rows so the block falls back
     * to its static content.
     *
     * @param array<string, mixed> $sourceArgs Source args (expects 'key').
     * @param mixed                $block      Block instance (context read defensively).
     * @param string               $attributeName Bound attribute name (unused; key selects the field).
     * @return string|int|null The field value, or null when unresolvable.
     */
    public function getBindingValue(array $sourceArgs, mixed $block, string $attributeName): string|int|null
    {
        $key = (string) ($sourceArgs['key'] ?? '');
        if ('' === $key) {
            return null;
        }

        $context = self::blockContext($block);
        $menuId = (int) ($context['smooth/menuId'] ?? 0);
        if ($menuId <= 0) {
            return null;
        }

        $menus = $this->container->make(MenuRepositoryInterface::class);
        $items = $this->container->make(MenuItemRepositoryInterface::class);
        $modifiers = $this->container->make(ModifierRepositoryInterface::class);
        if (
            ! $menus instanceof MenuRepositoryInterface
            || ! $items instanceof MenuItemRepositoryInterface
            || ! $modifiers instanceof ModifierRepositoryInterface
        ) {
            return null;
        }

        $itemId = (int) ($context['smooth/itemId'] ?? 0);
        $compute = fn (): string|int|null => $this->resolveBindingValue(
            $key,
            $menuId,
            $itemId,
            $menus,
            $items,
            $modifiers
        );

        $service = $this->container->make(MenuService::class);
        if (! $service instanceof MenuService) {
            return $compute();
        }

        $blogId = function_exists('get_current_blog_id') ? get_current_blog_id() : 1;

        return $service->remember(
            MenuService::cacheKey($blogId, 'binding', $menuId, $key . ':' . $itemId),
            $compute
        );
    }

    /**
     * Resolve a binding value from the live menu tables (unmemoized worker).
     *
     * @param string $key Field key (e.g. 'item/name').
     * @param int    $menuId Menu row id.
     * @param int    $itemId Menu item row id (0 when the block binds menu-level keys).
     * @return string|int|null The field value, or null when unresolvable.
     */
    private function resolveBindingValue(
        string $key,
        int $menuId,
        int $itemId,
        MenuRepositoryInterface $menus,
        MenuItemRepositoryInterface $items,
        ModifierRepositoryInterface $modifiers
    ): string|int|null {
        if (str_starts_with($key, 'menu/')) {
            $menu = $menus->findById($menuId);
            if (null === $menu) {
                return null;
            }
            return match ($key) {
                'menu/name' => isset($menu['name']) ? (string) $menu['name'] : null,
                'menu/description' => isset($menu['description']) ? (string) $menu['description'] : null,
                default => null,
            };
        }

        if ($itemId <= 0) {
            return null;
        }
        $item = $items->findById($itemId);
        if (null === $item || (int) ($item['menu_id'] ?? 0) !== $menuId) {
            return null;
        }

        return match ($key) {
            'item/name' => isset($item['name']) ? (string) $item['name'] : null,
            'item/description' => isset($item['description']) ? (string) $item['description'] : null,
            'item/price' => isset($item['price_cents']) ? MenuService::formatPrice((int) $item['price_cents']) : null,
            'item/price_raw' => isset($item['price_cents']) ? (int) $item['price_cents'] : null,
            'modifier/name', 'modifier/price' => $this->modifierValue($modifiers, $itemId, $key),
            default => null,
        };
    }

    /**
     * Read the first modifier value for an item (M1 scope).
     *
     * @param string $key modifier/name or modifier/price.
     * @return string|null
     */
    private function modifierValue(ModifierRepositoryInterface $modifiers, int $itemId, string $key): ?string
    {
        // Publish-only, like the public tree: editor-context discrimination
        // for draft modifiers is deferred to SMO-105/120 scope.
        $rows = $modifiers->listByItem($itemId, 'publish');
        if ([] === $rows) {
            return null;
        }
        $first = $rows[0];

        if ('modifier/price' === $key) {
            return isset($first['price_cents']) ? MenuService::formatPrice((int) $first['price_cents']) : null;
        }

        return isset($first['name']) ? (string) $first['name'] : null;
    }

    /**
     * Read block context defensively (WP_Block in production, arrays in unit tests).
     *
     * @return array<string, mixed>
     */
    private static function blockContext(mixed $block): array
    {
        if (\is_object($block) && isset($block->context) && \is_array($block->context)) {
            return $block->context;
        }
        if (\is_array($block) && isset($block['context']) && \is_array($block['context'])) {
            return $block['context'];
        }

        return [];
    }
}
