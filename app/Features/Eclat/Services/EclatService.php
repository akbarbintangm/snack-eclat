<?php

namespace App\Features\Eclat\Services;

use App\Features\Eclat\Interfaces\EclatRepositoryInterface;
use App\Features\Eclat\Models\EclatRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EclatService
{
    public function __construct(private readonly EclatRepositoryInterface $eclat) {}

    public function paginateRuns(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->eclat->paginateRuns($filters, $perPage);
    }

    public function paginateResults(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->eclat->paginateResults($filters, $perPage);
    }

    public function findRun(int $id): array
    {
        $run = $this->eclat->findRun($id);

        return $this->decorateRun($run);
    }

    public function analyze(float $minSupport, float $minConfidence): array
    {
        $startedAt = microtime(true);
        $transactions = $this->eclat->transactionsForAnalysis();
        $transactionItems = $this->buildTransactionItems($transactions);
        $totalTransactions = count($transactionItems);
        $verticalTidList = $this->buildVerticalTidList($transactionItems);
        $frequentItemsets = $this->mineFrequentItemsets($verticalTidList, max($totalTransactions, 1), $minSupport);
        $rules = $this->buildAssociationRules($frequentItemsets, $minConfidence);
        $executionTime = (int) round((microtime(true) - $startedAt) * 1000);

        $run = $this->eclat->storeAnalysis([
            'run_code' => 'ECLAT-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
            'min_support' => $minSupport,
            'min_confidence' => $minConfidence,
            'total_transactions' => $totalTransactions,
            'total_items' => count($verticalTidList),
            'frequent_itemset_count' => count($frequentItemsets),
            'rule_count' => count($rules),
            'execution_time_ms' => $executionTime,
            'tid_list' => $verticalTidList,
            'frequent_itemsets' => $frequentItemsets,
            'notes' => 'Analysis generated from active transactions using ECLAT vertical TID list and DFS intersection.',
            'status' => 'completed',
        ], $rules);

        return $this->decorateRun($run);
    }

    public function processSteps(EclatRun|array|null $run = null): array
    {
        $totalTransactions = $run instanceof EclatRun ? $run->total_transactions : ($run['total_transactions'] ?? 0);
        $frequentCount = $run instanceof EclatRun ? $run->frequent_itemset_count : ($run['frequent_itemset_count'] ?? 0);
        $ruleCount = $run instanceof EclatRun ? $run->rule_count : ($run['rule_count'] ?? 0);

        return [
            ['order' => 1, 'name' => 'Pengumpulan data transaksi', 'description' => 'Mengambil transaksi aktif dan detail snack.', 'metric' => $totalTransactions.' transaksi', 'status' => 'completed'],
            ['order' => 2, 'name' => 'Data cleaning', 'description' => 'Menghapus item kosong dan duplikasi snack dalam satu transaksi.', 'metric' => 'unique item per transaksi', 'status' => 'completed'],
            ['order' => 3, 'name' => 'Transformasi TID List', 'description' => 'Mengubah data horizontal menjadi daftar transaksi per snack.', 'metric' => 'vertical format', 'status' => 'completed'],
            ['order' => 4, 'name' => 'Frequent itemset', 'description' => 'Mencari itemset yang memenuhi minimum support.', 'metric' => $frequentCount.' itemset', 'status' => 'completed'],
            ['order' => 5, 'name' => 'DFS intersection', 'description' => 'Menggabungkan kandidat k-itemset dengan irisan TID List.', 'metric' => 'ECLAT recursion', 'status' => 'completed'],
            ['order' => 6, 'name' => 'Association rule', 'description' => 'Menghitung confidence dan lift ratio untuk rekomendasi.', 'metric' => $ruleCount.' rule', 'status' => 'completed'],
        ];
    }

    private function decorateRun(EclatRun $run): array
    {
        $run->loadMissing('results');

        return [
            'run' => $run,
            'steps' => $this->processSteps($run),
        ];
    }

    private function buildTransactionItems(Collection $transactions): array
    {
        return $transactions
            ->mapWithKeys(function ($transaction): array {
                $items = $transaction->details
                    ->filter(fn ($detail) => $detail->status === 'active' && $detail->snack?->status === 'active')
                    ->map(fn ($detail) => $detail->snack->name)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                return $items === [] ? [] : [$transaction->id => $items];
            })
            ->all();
    }

    private function buildVerticalTidList(array $transactionItems): array
    {
        $tidList = [];

        foreach ($transactionItems as $transactionId => $items) {
            foreach ($items as $item) {
                $tidList[$item] ??= [];
                $tidList[$item][] = (int) $transactionId;
            }
        }

        ksort($tidList);

        return $tidList;
    }

    private function mineFrequentItemsets(array $verticalTidList, int $totalTransactions, float $minSupport): array
    {
        $candidates = collect($verticalTidList)
            ->map(fn (array $tids, string $item): array => [
                'items' => [$item],
                'tids' => array_values(array_unique($tids)),
            ])
            ->sortBy(fn (array $candidate): string => $candidate['items'][0])
            ->values()
            ->all();

        $frequent = [];
        $this->eclatDfs([], $candidates, $totalTransactions, $minSupport, $frequent);

        return collect($frequent)
            ->sortByDesc('support')
            ->values()
            ->all();
    }

    private function eclatDfs(array $prefix, array $candidates, int $totalTransactions, float $minSupport, array &$frequent): void
    {
        $candidateCount = count($candidates);

        for ($i = 0; $i < $candidateCount; $i++) {
            $candidate = $candidates[$i];
            $items = array_values(array_unique([...$prefix, ...$candidate['items']]));
            sort($items);
            $tids = $candidate['tids'];
            $support = $this->percentage(count($tids), $totalTransactions);

            if ($support < $minSupport) {
                continue;
            }

            $frequent[] = $this->itemsetPayload($items, $tids, $support);

            $nextCandidates = [];
            for ($j = $i + 1; $j < $candidateCount; $j++) {
                $next = $candidates[$j];
                $intersection = array_values(array_intersect($tids, $next['tids']));
                $nextSupport = $this->percentage(count($intersection), $totalTransactions);

                if ($nextSupport >= $minSupport) {
                    $nextCandidates[] = [
                        'items' => $next['items'],
                        'tids' => $intersection,
                    ];
                }
            }

            if ($nextCandidates !== []) {
                $this->eclatDfs($items, $nextCandidates, $totalTransactions, $minSupport, $frequent);
            }
        }
    }

    private function buildAssociationRules(array $frequentItemsets, float $minConfidence): array
    {
        $supportMap = [];
        $countMap = [];

        foreach ($frequentItemsets as $itemset) {
            $key = $this->itemsetKey($itemset['items']);
            $supportMap[$key] = $itemset['support'];
            $countMap[$key] = $itemset['transaction_count'];
        }

        $rules = [];

        foreach ($frequentItemsets as $itemset) {
            if (count($itemset['items']) < 2) {
                continue;
            }

            foreach ($this->properSubsets($itemset['items']) as $antecedent) {
                $consequent = array_values(array_diff($itemset['items'], $antecedent));
                sort($antecedent);
                sort($consequent);

                $antecedentKey = $this->itemsetKey($antecedent);
                $consequentKey = $this->itemsetKey($consequent);

                if (! isset($countMap[$antecedentKey], $supportMap[$consequentKey])) {
                    continue;
                }

                $confidence = $this->percentage($itemset['transaction_count'], $countMap[$antecedentKey]);

                if ($confidence < $minConfidence) {
                    continue;
                }

                $liftRatio = $supportMap[$consequentKey] > 0 ? round($confidence / $supportMap[$consequentKey], 4) : null;
                $combination = implode(' dan ', $antecedent).' -> '.implode(' dan ', $consequent);

                $rules[] = [
                    'combination_item' => $combination,
                    'antecedent_items' => $antecedent,
                    'consequent_items' => $consequent,
                    'transaction_count' => $itemset['transaction_count'],
                    'support' => $itemset['support'],
                    'confidence' => $confidence,
                    'lift_ratio' => $liftRatio,
                    'tid_list' => $itemset['tid_list'],
                    'recommendation' => 'Pertimbangkan penataan atau paket promosi untuk '.$combination.'.',
                    'status' => 'active',
                ];
            }
        }

        return collect($rules)
            ->unique(fn (array $rule): string => $rule['combination_item'])
            ->sortByDesc('confidence')
            ->sortByDesc('support')
            ->values()
            ->all();
    }

    private function properSubsets(array $items): array
    {
        $subsets = [];
        $count = count($items);
        $limit = (1 << $count) - 1;

        for ($mask = 1; $mask < $limit; $mask++) {
            $subset = [];
            for ($index = 0; $index < $count; $index++) {
                if ($mask & (1 << $index)) {
                    $subset[] = $items[$index];
                }
            }
            $subsets[] = $subset;
        }

        return $subsets;
    }

    private function itemsetPayload(array $items, array $tids, float $support): array
    {
        return [
            'key' => $this->itemsetKey($items),
            'items' => $items,
            'label' => implode(', ', $items),
            'transaction_count' => count($tids),
            'support' => $support,
            'tid_list' => array_values($tids),
        ];
    }

    private function itemsetKey(array $items): string
    {
        sort($items);

        return implode('|', $items);
    }

    private function percentage(int $value, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($value / $total) * 100, 4);
    }
}
