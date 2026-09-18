<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Contracts\ModifierRepositoryInterface;
use SmoothRestaurant\Database\BaseRepository;

/**
 * Modifiers table repository.
 *
 * Owns modifier rows (item membership, name, cents price delta, display
 * order). Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class ModifierRepository extends BaseRepository implements ModifierRepositoryInterface
{
    protected function tableSuffix(): string
    {
        return 'smooth_modifiers';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'item_id', 'price_cents', 'sort_order'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "item_id bigint(20) unsigned NOT NULL,\n"
            . "name varchar(191) NOT NULL,\n"
            . "price_cents bigint(20) NOT NULL DEFAULT 0,\n"
            . "sort_order int(11) NOT NULL DEFAULT 0,\n"
            . "created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . "updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY item_sort (item_id, sort_order)';
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
    public function listByItem(int $itemId): array
    {
        return $this->mapRows(
            $this->fetchAll(
                $this->prepare(
                    'SELECT * FROM ' . $this->getTable()
                        . ' WHERE item_id = %d ORDER BY sort_order ASC, id ASC',
                    $itemId
                )
            )
        );
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
