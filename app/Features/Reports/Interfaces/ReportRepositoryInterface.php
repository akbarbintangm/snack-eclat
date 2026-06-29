<?php

namespace App\Features\Reports\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReportRepositoryInterface
{
    public function summary(): array;

    public function recommendations(array $filters = [], int $perPage = 10): LengthAwarePaginator;
}
