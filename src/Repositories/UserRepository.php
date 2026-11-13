<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Factories\UserFactory;
use App\Models\User;
use App\Storage\JsonStorage;

class UserRepository
{
    public function __construct(private JsonStorage $storage)
    {
    }

    /** @return User[] */
    public function all(): array
    {
        return array_map(
            static fn(array $row): User => UserFactory::fromArray($row),
            $this->storage->read()
        );
    }

    public function find(string $id): ?User
    {
        foreach ($this->all() as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }
        return null;
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

    public function save(User $user): void
    {
        $rows = $this->storage->read();
        $found = false;
        foreach ($rows as $i => $row) {
            if ($row['id'] === $user->getId()) {
                $rows[$i] = $user->toArray();
                $found = true;
                break;
            }
        }
        if (!$found) {
            $rows[] = $user->toArray();
        }
        $this->storage->write($rows);
    }

    public function nextId(): string
    {
        return 'U' . strtoupper(bin2hex(random_bytes(3)));
    }
}
