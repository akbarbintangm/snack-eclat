<?php

namespace App\Features\Transactions\Services;

use App\Features\Transactions\Interfaces\TransactionRepositoryInterface;
use App\Features\Transactions\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionService
{
    public function __construct(private readonly TransactionRepositoryInterface $transactions) {}

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->transactions->paginate($filters, $perPage);
    }

    public function find(int $id): Transaction
    {
        return $this->transactions->find($id);
    }

    public function create(array $payload): Transaction
    {
        $payload['status'] = $payload['status'] ?? 'active';
        $payload['reference_no'] = $payload['reference_no'] ?? null;

        return $this->transactions->createWithDetails($payload);
    }

    public function update(int $id, array $payload): Transaction
    {
        return $this->transactions->updateWithDetails($this->transactions->find($id), $payload);
    }

    public function delete(int $id): void
    {
        $this->transactions->delete($this->transactions->find($id));
    }
}
