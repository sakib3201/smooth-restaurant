<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Contracts\MenuRepositoryInterface;
use SmoothRestaurant\Database\BaseRepository;

/**
 * Menus table repository.
 *
 * Owns menu rows (name, slug, description, status, sort order). Items and
 * modifiers live in their own tables; tree assembly is MenuService's job.
 * Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot express.
 */
class MenuRepository extends BaseRepository implements MenuRepositoryInterface
{
    protected function tableSuffix(): string
    {
        return 'smooth_menus';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'sort_order'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "name varchar(191) NOT NULL,\n"
            . "slug varchar(191) NOT NULL,\n"
            . "description text NOT NULL,\n"
            . "status varchar(32) NOT NULL DEFAULT 'publish',\n"
            . "sort_order int(11) NOT NULL DEFAULT 0,\n"
            . "created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . "updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'UNIQUE KEY slug (slug),' . "\n"
            . 'KEY status_sort (status, sort_order)';
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
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        $row = $this->fetchRow(
            $this->prepare('SELECT * FROM ' . $this->getTable() . ' WHERE slug = %s', $slug)
        );
        if (null === $row) {
            return null;
        }

        return $this->mapRows([$row])[0];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function paginate(int $page, int $perPage, string $search = '', string $status = 'publish'): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $offset = ($page - 1) * $perPage;

        if ('' !== $search) {
            $like = '%' . $search . '%';
            $prepared = $this->prepare(
                'SELECT * FROM ' . $this->getTable()
                    . ' WHERE status = %s AND (name LIKE %s OR description LIKE %s)'
                    . ' ORDER BY sort_order ASC, id ASC LIMIT %d OFFSET %d',
                $status,
                $like,
                $like,
                $perPage,
                $offset
            );
        } else {
            $prepared = $this->prepare(
                'SELECT * FROM ' . $this->getTable()
                    . ' WHERE status = %s ORDER BY sort_order ASC, id ASC LIMIT %d OFFSET %d',
                $status,
                $perPage,
                $offset
            );
        }

        return $this->mapRows($this->fetchAll($prepared));
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

    public static function generateSlug(string $name): string
    {
        $slug = (string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $slug = trim($slug, '-');

        return '' !== $slug ? $slug : 'menu';
    }
}
