<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\StorageInterface;
use App\Models\Transaction;

/**
 * @extends BaseRepository<Transaction>
 */
class TransactionRepository extends BaseRepository
{
    public function __construct(StorageInterface $storage)
    {
        parent::__construct($storage);
    }

    protected function mapToEntity(array $row): Transaction
    {
        return Transaction::fromArray($row);
    }

    protected function getIdPrefix(): string
    {
        return 'T';
    }

    public function nextId(): string
    {
        return 'T' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
    }

    /** @return Transaction[] */
    public function all(): array
    {
        /** @var Transaction[] */
        return parent::all();
    }

    public function find(string $id): ?Transaction
    {
        $item = parent::find($id);
        return $item instanceof Transaction ? $item : null;
    }

    /** @return Transaction[] */
    public function byUser(string $userId): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn(Transaction $t): bool => $t->getUserId() === $userId
        ));
    }
}
