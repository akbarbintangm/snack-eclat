<?php

namespace App\Features\Snacks\Interfaces;

use App\Features\Snacks\Models\Snack;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SnackRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): Snack;

    public function create(array $payload): Snack;

    public function update(Snack $snack, array $payload): Snack;

    public function delete(Snack $snack): void;
}
