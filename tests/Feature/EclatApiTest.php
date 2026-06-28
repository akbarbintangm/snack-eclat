<?php

namespace Tests\Feature;

use App\Features\Snacks\Models\Snack;
use App\Features\Transactions\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EclatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_eclat_analysis_can_be_filtered_by_month(): void
    {
        $headers = $this->authHeaders();
        $popcorn = Snack::query()->create(['name' => 'Popcorn', 'status' => 'active']);
        $pia = Snack::query()->create(['name' => 'Pia', 'status' => 'active']);
        $kerupuk = Snack::query()->create(['name' => 'Kerupuk', 'status' => 'active']);

        $this->createTransaction('OCT-1', '2025-10-02', [$popcorn->id, $pia->id]);
        $this->createTransaction('OCT-2', '2025-10-03', [$popcorn->id, $pia->id]);
        $this->createTransaction('NOV-1', '2025-11-01', [$kerupuk->id]);

        $this->withHeaders($headers)
            ->postJson('/api/v1/eclat/analyze', [
                'min_support' => 50,
                'min_confidence' => 50,
                'filter_type' => 'month',
                'month' => '2025-10',
            ])
            ->assertCreated()
            ->assertJsonPath('data.run.filter_type', 'month')
            ->assertJsonPath('data.run.date_from', '2025-10-01')
            ->assertJsonPath('data.run.date_to', '2025-10-31')
            ->assertJsonPath('data.run.total_transactions', 2);

        $this->withHeaders($headers)
            ->getJson('/api/v1/eclat/results?filter_type=month&month=2025-10')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    private function createTransaction(string $reference, string $date, array $snackIds): void
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
