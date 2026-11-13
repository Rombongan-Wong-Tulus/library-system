<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Transaction;
use App\Storage\JsonStorage;

class TransactionRepository
{
    public function __construct(private JsonStorage $storage)
    {
    }

    /** @return Transaction[] */
    public function all(): array
    {
        return array_map(
            static fn(array $row): Transaction => Transaction::fromArray($row),
            $this->storage->read()
        );
    }

    public function find(string $id): ?Transaction
    {
        foreach ($this->all() as $trx) {
            if ($trx->getId() === $id) {
                return $trx;
            }
        }
        return null;
    }

    /** @return Transaction[] */
    public function byUser(string $userId): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn(Transaction $t): bool => $t->getUserId() === $userId
        ));
    }

    public function save(Transaction $trx): void
    {
        $rows = $this->storage->read();
        $found = false;
        foreach ($rows as $i => $row) {
            if ($row['id'] === $trx->getId()) {
                $rows[$i] = $trx->toArray();
                $found = true;
                break;
            }
        }
        if (!$found) {
            $rows[] = $trx->toArray();
        }
        $this->storage->write($rows);
    }

    public function nextId(): string
    {
        return 'T' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
    }
}
