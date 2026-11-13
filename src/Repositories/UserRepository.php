<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\StorageInterface;
use App\Factories\UserFactory;
use App\Models\User;

/**
 * @extends BaseRepository<User>
 */
class UserRepository extends BaseRepository
{
    public function __construct(StorageInterface $storage)
    {
        parent::__construct($storage);
    }

    protected function mapToEntity(array $row): User
    {
        return UserFactory::fromArray($row);
    }

    protected function getIdPrefix(): string
    {
        return 'U';
    }

    /** @return User[] */
    public function all(): array
    {
        /** @var User[] */
        return parent::all();
    }

    public function find(string $id): ?User
    {
        $item = parent::find($id);
        return $item instanceof User ? $item : null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->all() as $user) {
            if (strcasecmp($user->getEmail(), $email) === 0) {
                return $user;
            }
        }
        return null;
    }
}
