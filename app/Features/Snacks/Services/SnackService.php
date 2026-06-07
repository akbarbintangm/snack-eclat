<?php

namespace App\Features\Snacks\Services;

use App\Features\Snacks\Interfaces\SnackRepositoryInterface;
use App\Features\Snacks\Models\Snack;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SnackService
{
    public function __construct(private readonly SnackRepositoryInterface $snacks) {}

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->snacks->paginate($filters, $perPage);
    }

    public function find(int $id): Snack
    {
        return $this->snacks->find($id);
    }

    public function create(array $payload): Snack
    {
        $payload['status'] = $payload['status'] ?? 'active';

        return $this->snacks->create($payload);
    }

    public function update(int $id, array $payload): Snack
    {
        return $this->snacks->update($this->snacks->find($id), $payload);
    }

    public function delete(int $id): void
    {
        $this->snacks->delete($this->snacks->find($id));
    }
}
