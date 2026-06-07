<?php

namespace Database\Seeders;

use App\Features\Eclat\Models\EclatRun;
use App\Features\Eclat\Services\EclatService;
use App\Features\Snacks\Models\Snack;
use App\Features\Transactions\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate([
            'email' => 'admin@snack-eclat.local',
        ], [
            'name' => 'Test User',
            'password' => 'password',
        ]);

        $snacks = collect(['Pop U Corn OG', 'Pop U Corn Cheese', 'Pop U Corn Choco', 'Pia Kuno 10pcs'])
            ->mapWithKeys(fn (string $name): array => [
                $name => Snack::query()->firstOrCreate(['name' => $name], ['status' => 'active']),
            ]);

        $transactions = [
            'T1' => ['Pop U Corn OG', 'Pop U Corn Cheese'],
            'T2' => ['Pop U Corn Cheese', 'Pop U Corn Choco'],
            'T3' => ['Pop U Corn OG', 'Pia Kuno 10pcs'],
            'T4' => ['Pop U Corn Cheese', 'Pia Kuno 10pcs'],
            'T5' => ['Pop U Corn OG', 'Pop U Corn Choco'],
            'T6' => ['Pop U Corn OG', 'Pop U Corn Cheese', 'Pop U Corn Choco'],
            'T7' => ['Pop U Corn Cheese', 'Pia Kuno 10pcs'],
            'T8' => ['Pop U Corn OG', 'Pia Kuno 10pcs'],
            'T9' => ['Pop U Corn OG', 'Pop U Corn Cheese'],
            'T10' => ['Pop U Corn Choco', 'Pia Kuno 10pcs'],
            'T11' => ['Pop U Corn OG', 'Pop U Corn Cheese'],
            'T12' => ['Pop U Corn OG', 'Pop U Corn Choco'],
            'T13' => ['Pop U Corn Cheese', 'Pop U Corn Choco'],
            'T14' => ['Pop U Corn OG', 'Pia Kuno 10pcs'],
            'T15' => ['Pop U Corn Cheese', 'Pia Kuno 10pcs'],
            'T16' => ['Pop U Corn OG', 'Pop U Corn Cheese', 'Pia Kuno 10pcs'],
            'T17' => ['Pop U Corn Choco', 'Pia Kuno 10pcs'],
            'T18' => ['Pop U Corn OG', 'Pop U Corn Cheese'],
            'T19' => ['Pop U Corn OG', 'Pop U Corn Choco'],
            'T20' => ['Pop U Corn Cheese', 'Pia Kuno 10pcs'],
            'T21' => ['Pop U Corn OG', 'Pop U Corn Cheese'],
            'T22' => ['Pop U Corn Choco', 'Pia Kuno 10pcs'],
            'T23' => ['Pop U Corn OG', 'Pia Kuno 10pcs'],
            'T24' => ['Pop U Corn Cheese', 'Pop U Corn Choco'],
            'T25' => ['Pop U Corn OG', 'Pop U Corn Cheese', 'Pop U Corn Choco'],
        ];

        foreach ($transactions as $reference => $items) {
            $transaction = Transaction::query()->firstOrCreate([
                'reference_no' => $reference,
            ], [
                'transaction_date' => now()->subDays(26 - (int) str_replace('T', '', $reference))->toDateString(),
                'status' => 'active',
            ]);

            if ($transaction->details()->count() === 0) {
                $transaction->details()->createMany(array_map(fn (string $item): array => [
                    'snack_id' => $snacks[$item]->id,
                    'quantity' => 1,
                    'status' => 'active',
                ], $items));
            }
        }

        if (EclatRun::query()->count() === 0) {
            app(EclatService::class)->analyze(30, 50);
        }
    }
}
