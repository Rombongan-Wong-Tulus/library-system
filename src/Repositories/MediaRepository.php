<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Factories\MediaFactory;
use App\Models\Media;
use App\Storage\JsonStorage;

class MediaRepository
{
    public function __construct(private JsonStorage $storage)
    {
    }

    /** @return Media[] */
    public function all(): array
    {
        return array_map(
            static fn(array $row): Media => MediaFactory::fromArray($row),
            $this->storage->read()
        );
    }

    public function find(string $id): ?Media
    {
        foreach ($this->all() as $media) {
            if ($media->getId() === $id) {
                return $media;
            }
        }
        return null;
    }

    public function save(Media $media): void
    {
        $rows = $this->storage->read();
        $found = false;
        foreach ($rows as $i => $row) {
            if ($row['id'] === $media->getId()) {
                $rows[$i] = $media->toArray();
                $found = true;
                break;
            }
        }
        if (!$found) {
            $rows[] = $media->toArray();
        }
        $this->storage->write($rows);
    }

    public function delete(string $id): void
    {
        $rows = array_filter($this->storage->read(), static fn(array $r): bool => $r['id'] !== $id);
        $this->storage->write($rows);
    }

    public function nextId(): string
    {
        return 'M' . strtoupper(bin2hex(random_bytes(3)));
    }
}
