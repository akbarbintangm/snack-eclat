<?php

namespace App\Features\Eclat\Interfaces;

use App\Features\Eclat\Models\EclatRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EclatRepositoryInterface
{
    public function transactionsForAnalysis(array $period = []): Collection;

    public function transactionDateRange(): array;

    public function storeAnalysis(array $runPayload, array $rules): EclatRun;

    public function paginateRuns(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function findRun(int $id): EclatRun;

    public function paginateResults(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function latestRun(): ?EclatRun;
}
