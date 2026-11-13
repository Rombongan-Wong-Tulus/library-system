<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\StorageInterface;
use App\Factories\MediaFactory;
use App\Models\Media;

/**
 * @extends BaseRepository<Media>
 */
class MediaRepository extends BaseRepository
{
    public function __construct(StorageInterface $storage)
    {
        parent::__construct($storage);
    }

    protected function mapToEntity(array $row): Media
    {
        return MediaFactory::fromArray($row);
    }

    protected function getIdPrefix(): string
    {
        return 'M';
    }

    /** @return Media[] */
    public function all(): array
    {
        /** @var Media[] */
        return parent::all();
    }

    public function find(string $id): ?Media
    {
        $item = parent::find($id);
        return $item instanceof Media ? $item : null;
    }
}
