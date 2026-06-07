<?php

namespace App\Features\Snacks\Repositories;

use App\Features\Snacks\Interfaces\SnackRepositoryInterface;
use App\Features\Snacks\Models\Snack;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SnackRepository implements SnackRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Snack::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $id): Snack
    {
        return Snack::query()->findOrFail($id);
    }

    public function create(array $payload): Snack
    {
        return Snack::query()->create($payload);
    }

    public function update(Snack $snack, array $payload): Snack
    {
        $snack->fill($payload);
        $snack->save();

        return $snack->refresh();
    }

    public function delete(Snack $snack): void
    {
        $snack->delete();
    }
}
