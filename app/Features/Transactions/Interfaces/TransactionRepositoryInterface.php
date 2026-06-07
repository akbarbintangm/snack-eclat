<?php

namespace App\Features\Transactions\Interfaces;

use App\Features\Transactions\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): Transaction;

    public function createWithDetails(array $payload): Transaction;

    public function updateWithDetails(Transaction $transaction, array $payload): Transaction;

    public function delete(Transaction $transaction): void;
}
