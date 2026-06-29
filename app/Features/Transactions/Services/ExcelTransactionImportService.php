<?php

namespace App\Features\Transactions\Services;

use App\Features\Snacks\Models\Snack;
use App\Features\Transactions\Models\Transaction;
use App\Support\Excel\SimpleXlsxReader;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExcelTransactionImportService
{
    public function __construct(private readonly SimpleXlsxReader $reader) {}

    /**
     * @return array<string, int>
     */
    public function import(UploadedFile|string $file, ?int $userId = null): array
    {
        $path = $file instanceof UploadedFile ? (string) $file->getRealPath() : $file;
        $rows = $this->reader->read($path);
        $groups = $this->groupRows($rows);

        return DB::transaction(function () use ($groups, $rows, $userId): array {
            $createdTransactions = 0;
            $updatedTransactions = 0;
            $createdSnacks = 0;
            $detailCount = 0;

            foreach ($groups as $group) {
                $transaction = Transaction::query()->firstOrNew([
                    'reference_no' => $group['reference_no'],
                ]);

                $transaction->fill([
                    'user_id' => $userId,
                    'transaction_date' => $group['transaction_date'],
                    'status' => 'active',
                    'created_by' => $transaction->exists ? $transaction->created_by : $userId,
                    'updated_by' => $userId,
                ]);
                $transaction->save();

                $transaction->wasRecentlyCreated ? $createdTransactions++ : $updatedTransactions++;

                foreach ($group['items'] as $item) {
                    $snack = Snack::query()->firstOrCreate([
                        'name' => $item['name'],
                    ], [
                        'user_id' => $userId,
                        'status' => 'active',
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);

                    if ($snack->wasRecentlyCreated) {
                        $createdSnacks++;
                    }

                    $detail = $transaction->details()->withTrashed()->firstOrNew([
                        'snack_id' => $snack->id,
                    ]);

                    if ($detail->trashed()) {
                        $detail->restore();
                    }

                    $detail->fill([
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'status' => 'active',
                        'created_by' => $detail->exists ? $detail->created_by : $userId,
                        'updated_by' => $userId,
                    ]);
                    $detail->save();
                    $detailCount++;
                }
            }

            return [
                'rows_read' => count($rows),
                'rows_imported' => array_sum(array_map(fn (array $group): int => $group['row_count'], $groups)),
                'rows_skipped' => count($rows) - array_sum(array_map(fn (array $group): int => $group['row_count'], $groups)),
                'transactions_created' => $createdTransactions,
                'transactions_updated' => $updatedTransactions,
                'snacks_created' => $createdSnacks,
                'details_imported' => $detailCount,
            ];
        });
    }

    /**
     * @param array<int, array{sheet: string, row: int, values: array<string, string>}> $rows
     * @return array<string, array{reference_no: string, transaction_date: string, row_count: int, items: array<string, array{name: string, quantity: int, unit_price: float|null}>}>
     */
    private function groupRows(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $values = $row['values'];
            $product = $this->cleanProductName($values['nama_produk'] ?? '');
            $createdAt = $values['waktu_pesanan_dibuat'] ?? '';
            $date = $this->parseDate($createdAt);

            if ($product === '' || ! $date) {
                continue;
            }

            $key = implode('|', [
                $row['sheet'],
                $createdAt,
                $values['waktu_pembayaran_dilakukan'] ?? '',
                $values['username_pembeli'] ?? '',
                $values['waktu_pesanan_selesai'] ?? '',
            ]);
            $hash = strtoupper(substr(sha1($key), 0, 8));
            $referenceNo = 'XLS-'.$date->format('Ymd').'-'.$hash;
            $quantity = max(1, (int) preg_replace('/\D+/', '', $values['jumlah'] ?? '1'));
            $unitPrice = $this->parseMoney($values['harga_setelah_diskon'] ?? $values['harga_awal'] ?? null);

            $groups[$referenceNo] ??= [
                'reference_no' => $referenceNo,
                'transaction_date' => $date->toDateString(),
                'row_count' => 0,
                'items' => [],
            ];

            $groups[$referenceNo]['row_count']++;
            $itemKey = mb_strtolower($product);

            if (! isset($groups[$referenceNo]['items'][$itemKey])) {
                $groups[$referenceNo]['items'][$itemKey] = [
                    'name' => $product,
                    'quantity' => 0,
                    'unit_price' => $unitPrice,
                ];
            }

            $groups[$referenceNo]['items'][$itemKey]['quantity'] += $quantity;
            $groups[$referenceNo]['items'][$itemKey]['unit_price'] = $unitPrice ?? $groups[$referenceNo]['items'][$itemKey]['unit_price'];
        }

        return $groups;
    }

    private function cleanProductName(string $value): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', $value) ?: ''), 1024, '');
    }

    private function parseDate(string $value): ?CarbonImmutable
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return CarbonImmutable::create(1899, 12, 30)->addDays((int) $value);
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseMoney(?string $value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = preg_replace('/[^\d.,-]/', '', $value) ?: '';

        if (preg_match('/^-?\d{1,3}(\.\d{3})+(,\d+)?$/', $value)) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', $value)) {
            $value = str_replace(',', '', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
