<?php

namespace Tests\Feature;

use App\Features\Snacks\Models\Snack;
use App\Features\Transactions\Models\Transaction;
use App\Features\Transactions\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    private function authHeaders(): array
    {
        $token = Str::random(64);
        User::factory()->create([
            'api_token_hash' => hash('sha256', $token),
        ]);

        return ['Authorization' => 'Bearer '.$token];
    }
}
