<?php

namespace App\Features\Transactions\Repositories;

use App\Features\Transactions\Interfaces\TransactionRepositoryInterface;
use App\Features\Transactions\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Transaction::query()
            ->with(['details.snack'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('details.snack', function ($snackQuery) use ($search): void {
                        $snackQuery->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->when($filters['date_from'] ?? null, function ($query, string $date): void {
                $query->whereDate('transaction_date', '>=', $date);
            })
            ->when($filters['date_to'] ?? null, function ($query, string $date): void {
                $query->whereDate('transaction_date', '<=', $date);
            })
            ->latest('transaction_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function find(int $id): Transaction
    {
        return Transaction::query()->with(['details.snack'])->findOrFail($id);
    }

    public function createWithDetails(array $payload): Transaction
    {
        return DB::transaction(function () use ($payload): Transaction {
            $items = $payload['items'];
            unset($payload['items']);

            $transaction = Transaction::query()->create($payload);
            $transaction->details()->createMany($this->normalizeItems($items));

            return $transaction->load(['details.snack']);
        });
    }

    public function updateWithDetails(Transaction $transaction, array $payload): Transaction
    {
        return DB::transaction(function () use ($transaction, $payload): Transaction {
            $items = $payload['items'] ?? null;
            unset($payload['items']);

            $transaction->fill($payload);
            $transaction->save();

            if (is_array($items)) {
                $transaction->details()->withTrashed()->forceDelete();
                $transaction->details()->createMany($this->normalizeItems($items));
            }

            return $transaction->refresh()->load(['details.snack']);
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $transaction->details()->delete();
            $transaction->delete();
        });
    }

    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->map(fn (array $item): array => [
                'snack_id' => (int) $item['snack_id'],
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_price' => $item['unit_price'] ?? null,
                'status' => $item['status'] ?? 'active',
            ])
            ->values()
            ->all();
    }
}
