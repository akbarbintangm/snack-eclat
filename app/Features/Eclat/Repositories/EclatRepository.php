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
    public function transactionsForAnalysis(): Collection
    {
        return Transaction::query()
            ->with(['details.snack'])
            ->where('status', 'active')
            ->whereHas('details.snack', fn ($query) => $query->where('status', 'active'))
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
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function findRun(int $id): EclatRun
    {
        return EclatRun::query()->with('results')->findOrFail($id);
    }

    public function paginateResults(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return HasilEclat::query()
            ->with('run:id,run_code,min_support,min_confidence,created_at')
            ->when($filters['run_id'] ?? null, fn ($query, int $runId) => $query->where('eclat_run_id', $runId))
            ->when($filters['min_confidence'] ?? null, fn ($query, float $confidence) => $query->where('confidence', '>=', $confidence))
            ->when($filters['min_support'] ?? null, fn ($query, float $support) => $query->where('support', '>=', $support))
            ->where('status', 'active')
            ->orderByDesc('confidence')
            ->orderByDesc('support')
            ->paginate($perPage);
    }

    public function latestRun(): ?EclatRun
    {
        return EclatRun::query()->with('results')->latest('created_at')->first();
    }
}
