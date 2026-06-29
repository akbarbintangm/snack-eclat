<?php

namespace Tests\Feature;

use App\Features\Eclat\Models\EclatRun;
use App\Features\Eclat\Models\HasilEclat;
use App\Features\Snacks\Models\Snack;
use App\Features\Transactions\Models\Transaction;
use App\Features\Transactions\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesXlsxWorkbook;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use CreatesXlsxWorkbook;
    use RefreshDatabase;

    public function test_user_can_create_and_list_transaction(): void
    {
        $headers = $this->authHeaders();

        $snack = $this->withHeaders($headers)
            ->postJson('/api/v1/snacks', ['name' => 'Popcorn Original'])
            ->assertCreated()
            ->json('data');

        $this->withHeaders($headers)
            ->postJson('/api/v1/transactions', [
                'reference_no' => 'TRX-001',
                'transaction_date' => '2026-01-02',
                'items' => [
                    ['snack_id' => $snack['id'], 'quantity' => 2],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.reference_no', 'TRX-001')
            ->assertJsonPath('data.details.0.quantity', 2);

        $this->assertDatabaseHas('transactions', ['reference_no' => 'TRX-001']);
        $this->assertDatabaseHas('transaction_details', ['snack_id' => $snack['id'], 'quantity' => 2]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/transactions?search=Popcorn')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_transactions_can_be_searched_filtered_by_date_range_and_sorted_by_latest_update(): void
    {
        $headers = $this->authHeaders();
        $popcorn = Snack::query()->create(['name' => 'Popcorn Caramel', 'status' => 'active']);
        $pia = Snack::query()->create(['name' => 'Pia Kuno', 'status' => 'active']);

        $older = $this->createTransaction('REF-OLD', '2026-01-01', [$popcorn->id]);
        $newer = $this->createTransaction('REF-NEW', '2026-02-02', [$pia->id]);

        DB::table('transactions')->where('id', $older->id)->update(['updated_at' => '2026-01-01 08:00:00']);
        DB::table('transactions')->where('id', $newer->id)->update(['updated_at' => '2026-01-02 08:00:00']);

        $this->withHeaders($headers)
            ->getJson('/api/v1/transactions?per_page=10')
            ->assertOk()
            ->assertJsonPath('data.0.reference_no', 'REF-NEW');

        $this->withHeaders($headers)
            ->getJson('/api/v1/transactions?search=Pia')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.reference_no', 'REF-NEW');

        $this->withHeaders($headers)
            ->getJson('/api/v1/transactions?date_from=2026-01-01&date_to=2026-01-31')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.reference_no', 'REF-OLD');

        $this->withHeaders($headers)
            ->getJson('/api/v1/transactions?search=REF-NEW&date_from=2026-01-01&date_to=2026-01-31')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    public function test_user_can_import_transactions_from_excel(): void
    {
        $headers = $this->authHeaders();
        $path = $this->createXlsxWorkbook([
            ['Waktu Pesanan Dibuat', 'Waktu Pembayaran Dilakukan', 'Metode Pembayaran', 'Nama Produk', 'Harga Awal', 'Harga Setelah Diskon', 'Jumlah', 'Subtotal Pesanan', 'Username (Pembeli)', 'Kota/Kabupaten', 'Provinsi', 'Waktu Pesanan Selesai'],
            ['2025-10-02 10:29', '2025-10-02 10:35', 'Saldo ShopeePay', 'Popcorn Caramel', '80.000', '37.000', '2', '74.000', 'buyer_a', 'KAB. GRESIK', 'JAWA TIMUR', '2025-10-03 15:23'],
            ['2025-10-02 10:29', '2025-10-02 10:35', 'Saldo ShopeePay', 'Pia Kuno', '100.000', '54.000', '1', '54.000', 'buyer_a', 'KAB. GRESIK', 'JAWA TIMUR', '2025-10-03 15:23'],
            ['2025-11-01 11:31', '2025-11-01 11:32', 'Saldo ShopeePay', 'Popcorn Original', '60.000', '37.000', '1', '37.000', 'buyer_b', 'KOTA SURABAYA', 'JAWA TIMUR', '2025-11-03 14:40'],
        ]);
        $file = new UploadedFile($path, 'transactions.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->withHeaders($headers)
            ->post('/api/v1/transactions/import', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.rows_read', 3)
            ->assertJsonPath('data.rows_imported', 3)
            ->assertJsonPath('data.transactions_created', 2)
            ->assertJsonPath('data.snacks_created', 3);

        $this->assertSame(2, Transaction::query()->count());
        $this->assertSame(3, Snack::query()->count());
        $this->assertSame(3, TransactionDetail::query()->count());
        $this->assertDatabaseHas('transaction_details', ['quantity' => 2, 'unit_price' => 37000]);
    }

    public function test_user_can_delete_all_transaction_data_and_related_eclat_results(): void
    {
        $headers = $this->authHeaders();
        $popcorn = Snack::query()->create(['name' => 'Popcorn', 'status' => 'active']);
        $pia = Snack::query()->create(['name' => 'Pia', 'status' => 'active']);

        $this->createTransaction('DEL-1', '2026-01-01', [$popcorn->id, $pia->id]);
        $this->createTransaction('DEL-2', '2026-01-02', [$popcorn->id, $pia->id]);

        $this->withHeaders($headers)
            ->postJson('/api/v1/eclat/analyze', [
                'min_support' => 50,
                'min_confidence' => 50,
                'filter_type' => 'all',
            ])
            ->assertCreated()
            ->assertJsonPath('data.run.rule_count', 2);

        $this->withHeaders($headers)
            ->deleteJson('/api/v1/transactions')
            ->assertOk()
            ->assertJsonPath('data.transactions_deleted', 2)
            ->assertJsonPath('data.transaction_details_deleted', 4)
            ->assertJsonPath('data.analysis_runs_deleted', 1)
            ->assertJsonPath('data.analysis_rules_deleted', 2);

        $this->assertSame(2, Snack::query()->count());
        $this->assertSame(0, Transaction::query()->withTrashed()->count());
        $this->assertSame(0, TransactionDetail::query()->withTrashed()->count());
        $this->assertSame(0, EclatRun::query()->withTrashed()->count());
        $this->assertSame(0, HasilEclat::query()->withTrashed()->count());
    }

    private function createTransaction(string $reference, string $date, array $snackIds): Transaction
    {
        $transaction = Transaction::query()->create([
            'reference_no' => $reference,
            'transaction_date' => $date,
            'status' => 'active',
        ]);

        foreach ($snackIds as $snackId) {
            $transaction->details()->create([
                'snack_id' => $snackId,
                'quantity' => 1,
                'status' => 'active',
            ]);
        }

        return $transaction;
    }

    private function authHeaders(): array
    {
        $token = Str::random(64);
        User::factory()->create([
            'api_token_hash' => hash('sha256', $token),
        ]);

        return ['Authorization' => 'Bearer '.$token];
    }
}
