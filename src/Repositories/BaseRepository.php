<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\StorageInterface;

/**
 * Repository abstrak dasar untuk entitas dengan ID.
 */
abstract class BaseRepository
{
    public function __construct(protected StorageInterface $storage)
    {
    }

    /** Ubah baris array JSON menjadi objek entitas domain. */
    abstract protected function mapToEntity(array $row): object;

    /** Awalan ID untuk entitas domain (contoh: 'M', 'U', 'T'). */
    abstract protected function getIdPrefix(): string;

    /** @return object[] */
    public function all(): array
    {
        return array_map(
            fn(array $row): object => $this->mapToEntity($row),
            $this->storage->read()
        );
    }

    public function find(string $id): ?object
    {
        foreach ($this->all() as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }
        return null;
    }

    public function save(object $entity): void
    {
        $this->storage->transaction(function (array $rows) use ($entity): array {
            $found = false;
            $data = $entity->toArray();
            foreach ($rows as $i => $row) {
                if ($row['id'] === $entity->getId()) {
                    $rows[$i] = $data;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $rows[] = $data;
            }
            return $rows;
        });
    }

    public function delete(string $id): void
    {
        $this->storage->transaction(function (array $rows) use ($id): array {
            return array_values(array_filter($rows, static fn(array $r): bool => $r['id'] !== $id));
        });
    }

    public function nextId(): string
    {
        return $this->getIdPrefix() . strtoupper(bin2hex(random_bytes(3)));
    }
}
