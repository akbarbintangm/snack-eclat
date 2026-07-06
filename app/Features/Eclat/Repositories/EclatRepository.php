<?php

namespace App\Features\Eclat\Repositories;

use App\Features\Eclat\Interfaces\EclatRepositoryInterface;
use App\Features\Eclat\Models\EclatRun;
use App\Features\Eclat\Models\HasilEclat;
use App\Features\Transactions\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EclatRepository implements EclatRepositoryInterface
{
    public function transactionsForAnalysis(array $period = []): Collection
    {
        return Transaction::query()
            ->with(['details.snack'])
            ->where('status', 'active')
            ->whereHas('details.snack', fn ($query) => $query->where('status', 'active'))
            ->when($period['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($period['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('transaction_date', '<=', $date))
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
    }

    public function storeAnalysis(array $runPayload, array $rules): EclatRun
    {
        return DB::transaction(function () use ($runPayload, $rules): EclatRun {
            $run = EclatRun::query()->create($runPayload);

            if ($rules !== []) {
                $run->results()->createMany($rules);
            }

            return $run->load('results');
        });
    }

    public function paginateRuns(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return EclatRun::query()
            ->withCount('results')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('run_code', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when(($filters['filter_type'] ?? 'all') !== 'all', fn ($query) => $this->applyRunPeriodFilter($query, $filters))
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function findRun(int $id): EclatRun
    {
        return EclatRun::query()->with('results')->findOrFail($id);
    }

    public function paginateResults(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $runId = $filters['run_id'] ?? null;

        return HasilEclat::query()
            ->with('run:id,run_code,min_support,min_confidence,filter_type,date_from,date_to,created_at')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('combination_item', 'like', "%{$search}%")
                        ->orWhere('recommendation', 'like', "%{$search}%");
                });
            })
            ->when($runId, fn ($query, int $selectedRunId) => $query->where('eclat_run_id', $selectedRunId))
            ->when($filters['min_confidence'] ?? null, fn ($query, float $confidence) => $query->where('confidence', '>=', $confidence))
            ->when($filters['min_support'] ?? null, fn ($query, float $support) => $query->where('support', '>=', $support))
            ->when(! $runId && ($filters['filter_type'] ?? 'all') !== 'all', function ($query) use ($filters): void {
                $query->whereHas('run', fn ($runQuery) => $this->applyRunPeriodFilter($runQuery, $filters));
            })
            ->where('status', 'active')
            ->orderByDesc('confidence')
            ->orderByDesc('support')
            ->paginate($perPage);
    }

    public function latestRun(): ?EclatRun
    {
        return EclatRun::query()->with('results')->latest('created_at')->first();
    }

    private function applyRunPeriodFilter($query, array $filters): void
    {
        $query->when($filters['date_from'] ?? null, fn ($dateQuery, string $date) => $dateQuery->whereDate('date_from', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($dateQuery, string $date) => $dateQuery->whereDate('date_to', '<=', $date));
    }
}
