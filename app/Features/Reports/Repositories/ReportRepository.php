<?php

namespace App\Features\Reports\Repositories;

use App\Features\Eclat\Models\EclatRun;
use App\Features\Eclat\Models\HasilEclat;
use App\Features\Reports\Interfaces\ReportRepositoryInterface;
use App\Features\Snacks\Models\Snack;
use App\Features\Transactions\Models\Transaction;
use App\Features\Transactions\Models\TransactionDetail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportRepository implements ReportRepositoryInterface
{
    public function summary(): array
    {
        $latestRun = EclatRun::query()->latest('created_at')->first();
        $topRule = HasilEclat::query()
            ->where('status', 'active')
            ->orderByDesc('confidence')
            ->orderByDesc('support')
            ->first();

        return [
            'snack_count' => Snack::query()->where('status', 'active')->count(),
            'transaction_count' => Transaction::query()->where('status', 'active')->count(),
            'transaction_item_count' => TransactionDetail::query()->where('status', 'active')->count(),
            'analysis_run_count' => EclatRun::query()->count(),
            'latest_run' => $latestRun,
            'top_rule' => $topRule,
        ];
    }

    public function recommendations(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return HasilEclat::query()
            ->with('run:id,run_code,min_support,min_confidence,created_at')
            ->where('status', 'active')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('combination_item', 'like', "%{$search}%")
                        ->orWhere('recommendation', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('confidence')
            ->orderByDesc('support')
            ->paginate($perPage);
    }
}
