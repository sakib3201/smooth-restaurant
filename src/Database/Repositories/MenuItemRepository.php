<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Contracts\MenuItemRepositoryInterface;
use SmoothRestaurant\Database\BaseRepository;

/**
 * Menu items table repository.
 *
 * Owns item rows (menu membership, name, description, cents price, display
 * order). Prices are read live at checkout calculation time — no snapshots.
 * Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot express.
 */
class MenuItemRepository extends BaseRepository implements MenuItemRepositoryInterface
{
    protected function tableSuffix(): string
    {
        return 'smooth_menu_items';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'menu_id', 'price_cents', 'image_id', 'sort_order'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "menu_id bigint(20) unsigned NOT NULL,\n"
            . "name varchar(191) NOT NULL,\n"
            . "description text NOT NULL,\n"
            . "price_cents bigint(20) NOT NULL DEFAULT 0,\n"
            . "image_id bigint(20) unsigned NOT NULL DEFAULT 0,\n"
            . "status varchar(32) NOT NULL DEFAULT 'publish',\n"
            . "sort_order int(11) NOT NULL DEFAULT 0,\n"
            . "created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . "updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY menu_status_sort (menu_id, status, sort_order)';
    }

    /**
     * @param array<string, mixed> $data Column values (without id).
     */
    public function insert(array $data): int
    {
        return $this->insertRow($this->getTable(), $data);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $row = $this->fetchRow(
            $this->prepare('SELECT * FROM ' . $this->getTable() . ' WHERE id = %d', $id)
        );
        if (null === $row) {
            return null;
        }

        return $this->mapRows([$row])[0];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByMenu(int $menuId, string $status = 'publish'): array
    {
        return $this->mapRows(
            $this->fetchAll(
                $this->prepare(
                    'SELECT * FROM ' . $this->getTable()
                        . ' WHERE menu_id = %d AND status = %s ORDER BY sort_order ASC, id ASC',
                    $menuId,
                    $status
                )
            )
        );
    }

    /**
     * @return int|null Highest sort_order, or null when the menu has no items.
     */
    public function maxSortOrderForMenu(int $menuId): ?int
    {
        $row = $this->fetchRow(
            $this->prepare(
                'SELECT MAX(sort_order) AS max_order FROM ' . $this->getTable() . ' WHERE menu_id = %d',
                $menuId
            )
        );
        $max = $row['max_order'] ?? null;

        return \is_numeric($max) ? (int) $max : null;
    }

    /**
     * @param array<string, mixed> $data New column values.
     */
    public function update(int $id, array $data): bool
    {
        return $this->updateRows($this->getTable(), $data, ['id' => $id]) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->deleteRows($this->getTable(), ['id' => $id]) > 0;
    }
}
