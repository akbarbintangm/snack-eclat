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

    public function test_eclat_analysis_can_be_filtered_by_date_range(): void
    {
        $headers = $this->authHeaders();
        $popcorn = Snack::query()->create(['name' => 'Popcorn', 'status' => 'active']);
        $pia = Snack::query()->create(['name' => 'Pia', 'status' => 'active']);
        $kerupuk = Snack::query()->create(['name' => 'Kerupuk', 'status' => 'active']);

        $this->createTransaction('OCT-1', '2025-10-02', [$popcorn->id, $pia->id]);
        $this->createTransaction('OCT-2', '2025-10-03', [$popcorn->id, $pia->id]);
        $this->createTransaction('NOV-1', '2025-11-01', [$kerupuk->id]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/eclat/transaction-period')
            ->assertOk()
            ->assertJsonPath('data.date_from', '2025-10-02')
            ->assertJsonPath('data.date_to', '2025-11-01')
            ->assertJsonPath('data.total_transactions', 3);

        $this->withHeaders($headers)
            ->postJson('/api/v1/eclat/analyze', [
                'min_support' => 50,
                'min_confidence' => 50,
                'filter_type' => 'range',
                'date_from' => '2025-10-01',
                'date_to' => '2025-10-31',
            ])
            ->assertCreated()
            ->assertJsonPath('data.run.filter_type', 'range')
            ->assertJsonPath('data.run.date_from', '2025-10-01')
            ->assertJsonPath('data.run.date_to', '2025-10-31')
            ->assertJsonPath('data.run.total_transactions', 2);

        $this->withHeaders($headers)
            ->getJson('/api/v1/eclat/results?filter_type=range&date_from=2025-10-01&date_to=2025-10-31')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->withHeaders($headers)
            ->getJson('/api/v1/eclat/runs?search=ECLAT')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->withHeaders($headers)
            ->getJson('/api/v1/eclat/results?search=Popcorn&filter_type=range&date_from=2025-10-01&date_to=2025-10-31')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->withHeaders($this->authHeaders('owner'))
            ->getJson('/api/v1/reports/recommendations?search=Popcorn&per_page=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(1, 'data');
    }

    public function test_eclat_analysis_accepts_long_excel_snack_names(): void
    {
        $headers = $this->authHeaders();
        $almond = Snack::query()->create([
            'name' => 'Milkirp Cookies Kue Kering Camilan Kue Bolu Kering Rasa Milk Roombutter Susu Premium Box Panjang',
            'status' => 'active',
        ]);
        $popcorn = Snack::query()->create([
            'name' => 'Popcorn Rasa Caramel Coklat Keju Black Truffle Japanese Nori Premium Jagung Popcorn Camilan Snack Jagung 300gr',
            'status' => 'active',
        ]);

        $this->createTransaction('LONG-1', '2025-12-04', [$almond->id, $popcorn->id]);
        $this->createTransaction('LONG-2', '2025-12-14', [$almond->id, $popcorn->id]);

        $this->withHeaders($headers)
            ->postJson('/api/v1/eclat/analyze', [
                'min_support' => 50,
                'min_confidence' => 50,
                'filter_type' => 'all',
            ])
            ->assertCreated()
            ->assertJsonPath('data.run.rule_count', 2);

        $this->assertDatabaseHas('hasil_eclat', [
            'support' => 100,
            'confidence' => 100,
        ]);
    }

    public function test_eclat_results_stay_scoped_to_selected_run_when_period_filter_is_present(): void
    {
        $headers = $this->authHeaders();
        $popcorn = Snack::query()->create(['name' => 'Popcorn', 'status' => 'active']);
        $pia = Snack::query()->create(['name' => 'Pia', 'status' => 'active']);
        $keripik = Snack::query()->create(['name' => 'Keripik', 'status' => 'active']);

        $this->createTransaction('OCT-1', '2025-10-02', [$popcorn->id, $pia->id]);
        $this->createTransaction('OCT-2', '2025-10-03', [$popcorn->id, $pia->id]);
        $this->createTransaction('NOV-1', '2025-11-01', [$popcorn->id, $keripik->id]);
        $this->createTransaction('NOV-2', '2025-11-02', [$popcorn->id, $keripik->id]);

        $octoberRunId = $this->withHeaders($headers)
            ->postJson('/api/v1/eclat/analyze', [
                'min_support' => 50,
                'min_confidence' => 50,
                'filter_type' => 'range',
                'date_from' => '2025-10-01',
                'date_to' => '2025-10-31',
            ])
            ->assertCreated()
            ->assertJsonPath('data.run.min_support', 50)
            ->assertJsonPath('data.run.min_confidence', 50)
            ->assertJsonPath('data.run.rule_count', 2)
            ->json('data.run.id');

        $this->withHeaders($headers)
            ->postJson('/api/v1/eclat/analyze', [
                'min_support' => 0.1,
                'min_confidence' => 30,
                'filter_type' => 'all',
            ])
            ->assertCreated()
            ->assertJsonPath('data.run.min_support', 0.1)
            ->assertJsonPath('data.run.min_confidence', 30)
            ->assertJsonPath('data.run.rule_count', 4);

        $this->withHeaders($headers)
            ->getJson("/api/v1/eclat/results?run_id={$octoberRunId}&filter_type=range&date_from=2025-11-01&date_to=2025-11-30")
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.run.min_support', 50)
            ->assertJsonPath('data.0.run.min_confidence', 50);
    }

    public function test_eclat_analysis_matches_proposal_example_dataset(): void
    {
        $headers = $this->authHeaders();
        $og = Snack::query()->create(['name' => 'Pop U Corn OG', 'status' => 'active']);
        $cheese = Snack::query()->create(['name' => 'Pop U Corn Cheese', 'status' => 'active']);
        $choco = Snack::query()->create(['name' => 'Pop U Corn Choco', 'status' => 'active']);
        $pia = Snack::query()->create(['name' => 'Pia Kuno 10pcs', 'status' => 'active']);

        $dataset = [
            'T1' => [$og->id, $cheese->id],
            'T2' => [$cheese->id, $choco->id],
            'T3' => [$og->id, $pia->id],
            'T4' => [$cheese->id, $pia->id],
            'T5' => [$og->id, $choco->id],
            'T6' => [$og->id, $cheese->id, $choco->id],
            'T7' => [$cheese->id, $pia->id],
            'T8' => [$og->id, $pia->id],
            'T9' => [$og->id, $cheese->id],
            'T10' => [$choco->id, $pia->id],
            'T11' => [$og->id, $cheese->id],
            'T12' => [$og->id, $choco->id],
            'T13' => [$cheese->id, $choco->id],
            'T14' => [$og->id, $pia->id],
            'T15' => [$cheese->id, $pia->id],
            'T16' => [$og->id, $cheese->id, $pia->id],
            'T17' => [$choco->id, $pia->id],
            'T18' => [$og->id, $cheese->id],
            'T19' => [$og->id, $choco->id],
            'T20' => [$cheese->id, $pia->id],
            'T21' => [$og->id, $cheese->id],
            'T22' => [$choco->id, $pia->id],
            'T23' => [$og->id, $pia->id],
            'T24' => [$cheese->id, $choco->id],
            'T25' => [$og->id, $cheese->id, $choco->id],
        ];

        foreach ($dataset as $reference => $snackIds) {
            $this->createTransaction($reference, '2025-12-04', $snackIds);
        }

        $this->withHeaders($headers)
            ->postJson('/api/v1/eclat/analyze', [
                'min_support' => 30,
                'min_confidence' => 50,
                'filter_type' => 'all',
            ])
            ->assertCreated()
            ->assertJsonPath('data.run.total_transactions', 25)
            ->assertJsonPath('data.run.frequent_itemset_count', 5)
            ->assertJsonPath('data.run.rule_count', 2)
            ->assertJsonPath('data.run.results.0.support', 32)
            ->assertJsonPath('data.run.results.0.confidence', 53.3333);
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

    private function authHeaders(string $level = 'admin'): array
    {
        $token = Str::random(64);
        User::factory()->create([
            'level' => $level,
            'api_token_hash' => hash('sha256', $token),
        ]);

        return ['Authorization' => 'Bearer '.$token];
    }
}
