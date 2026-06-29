<?php

namespace Database\Seeders;

use App\Features\Eclat\Models\EclatRun;
use App\Features\Eclat\Services\EclatService;
use App\Features\Snacks\Models\Snack;
use App\Features\Transactions\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = $this->seedUser('admin', 'Admin Snack Eclat', 'admin@snack-eclat.local', 'admin');
        $this->seedUser('owner', 'Owner Snack Eclat', 'owner@snack-eclat.local', 'owner');

        $snacks = collect([
            'PROMO BUNDLE POPCORN 3 RASA - Coklat, Caramel, Keju | Camilan Snack Jagung 300gr',
            'Popcorn Rasa Caramel Coklat Keju Premium Jagung Popcorn Camilan Snack Jagung 300gr',
            'Popcorn Rasa Keju (Cheese) Premium / Jagung Popcorn Camilan Snack Jagung 300gr',
            'Popcorn Rasa Original Premium / Jagung Popcorn Camilan Snack Jagung 300gr TERSEDIA GROSIR',
            'Pia Rasa Kuno isi 10 Bakpia Kacang Hijau, Coklat, Keju Bakpia Kering Kue Pia Oleh Oleh Surabaya',
            'Pia Rasa Kuno (Kacang Hijau, Coklat, Keju) Bakpia Kering Oleh Oleh Khas Surabaya',
            'Kerupuk Upil Bumbu Rujak / Kerupuk Pasir / Cemilan Kerupuk Pedas Manis Cap Kepala Singa 150 gr',
            'Roti Gabin Special Cap Kepala Singa 250 gr',
            'Milkrip Cookies Kue Kering Camilan Kue Bolu Kering Rasa Milk Roombutter Susu',
            'Keripik Pisang Manis Cemilan Kripik Pisang Madu Camilan Cap Kepala Singa 200 gr',
            'Sambal Bawang Primarasa Khas Surabaya Tradisional 150 gr',
            'Keripik Paru Sapi Camilan Kerupuk Paru Goreng Cap Kepala Singa Khas Surabaya 100 gr',
            'Bumbu Pecel Madiun Asli Sambel Pecel Cap Kepala Singa 200 gr',
            'Pop U Corn OG',
            'Pop U Corn Cheese',
            'Pop U Corn Choco',
            'Pia Kuno 10pcs',
        ])
            ->mapWithKeys(fn (string $name): array => [
                $name => Snack::query()->firstOrCreate(['name' => $name], [
                    'user_id' => $admin->id,
                    'status' => 'active',
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]),
            ]);

        $transactions = [
            ['reference' => 'SEED-20251002-001', 'date' => '2025-10-02', 'items' => [
                'PROMO BUNDLE POPCORN 3 RASA - Coklat, Caramel, Keju | Camilan Snack Jagung 300gr',
                'Popcorn Rasa Keju (Cheese) Premium / Jagung Popcorn Camilan Snack Jagung 300gr',
            ]],
            ['reference' => 'SEED-20251002-002', 'date' => '2025-10-02', 'items' => [
                'Kerupuk Upil Bumbu Rujak / Kerupuk Pasir / Cemilan Kerupuk Pedas Manis Cap Kepala Singa 150 gr',
                'Roti Gabin Special Cap Kepala Singa 250 gr',
                'Bumbu Pecel Madiun Asli Sambel Pecel Cap Kepala Singa 200 gr',
            ]],
            ['reference' => 'SEED-20251003-001', 'date' => '2025-10-03', 'items' => [
                'Popcorn Rasa Caramel Coklat Keju Premium Jagung Popcorn Camilan Snack Jagung 300gr',
                'Popcorn Rasa Keju (Cheese) Premium / Jagung Popcorn Camilan Snack Jagung 300gr',
            ]],
            ['reference' => 'SEED-20251005-001', 'date' => '2025-10-05', 'items' => [
                'Pia Rasa Kuno isi 10 Bakpia Kacang Hijau, Coklat, Keju Bakpia Kering Kue Pia Oleh Oleh Surabaya',
                'Keripik Paru Sapi Camilan Kerupuk Paru Goreng Cap Kepala Singa Khas Surabaya 100 gr',
            ]],
            ['reference' => 'SEED-20251101-001', 'date' => '2025-11-01', 'items' => [
                'Popcorn Rasa Original Premium / Jagung Popcorn Camilan Snack Jagung 300gr TERSEDIA GROSIR',
                'Popcorn Rasa Keju (Cheese) Premium / Jagung Popcorn Camilan Snack Jagung 300gr',
            ]],
            ['reference' => 'SEED-20251103-001', 'date' => '2025-11-03', 'items' => [
                'Keripik Pisang Manis Cemilan Kripik Pisang Madu Camilan Cap Kepala Singa 200 gr',
                'Kerupuk Upil Bumbu Rujak / Kerupuk Pasir / Cemilan Kerupuk Pedas Manis Cap Kepala Singa 150 gr',
            ]],
            ['reference' => 'SEED-20251106-001', 'date' => '2025-11-06', 'items' => [
                'Pia Rasa Kuno (Kacang Hijau, Coklat, Keju) Bakpia Kering Oleh Oleh Khas Surabaya',
                'Kerupuk Upil Bumbu Rujak / Kerupuk Pasir / Cemilan Kerupuk Pedas Manis Cap Kepala Singa 150 gr',
            ]],
            ['reference' => 'SEED-20251202-001', 'date' => '2025-12-02', 'items' => [
                'Pia Rasa Kuno (Kacang Hijau, Coklat, Keju) Bakpia Kering Oleh Oleh Khas Surabaya',
                'Kerupuk Upil Bumbu Rujak / Kerupuk Pasir / Cemilan Kerupuk Pedas Manis Cap Kepala Singa 150 gr',
            ]],
            ['reference' => 'SEED-20251202-002', 'date' => '2025-12-02', 'items' => [
                'Popcorn Rasa Caramel Coklat Keju Premium Jagung Popcorn Camilan Snack Jagung 300gr',
                'Popcorn Rasa Keju (Cheese) Premium / Jagung Popcorn Camilan Snack Jagung 300gr',
            ]],
            ['reference' => 'SEED-20251203-001', 'date' => '2025-12-03', 'items' => [
                'PROMO BUNDLE POPCORN 3 RASA - Coklat, Caramel, Keju | Camilan Snack Jagung 300gr',
                'Pia Rasa Kuno isi 10 Bakpia Kacang Hijau, Coklat, Keju Bakpia Kering Kue Pia Oleh Oleh Surabaya',
            ]],
            ['reference' => 'SEED-20260101-001', 'date' => '2026-01-01', 'items' => [
                'Popcorn Rasa Caramel Coklat Keju Premium Jagung Popcorn Camilan Snack Jagung 300gr',
                'Milkrip Cookies Kue Kering Camilan Kue Bolu Kering Rasa Milk Roombutter Susu',
            ]],
            ['reference' => 'SEED-20260102-001', 'date' => '2026-01-02', 'items' => [
                'Popcorn Rasa Caramel Coklat Keju Premium Jagung Popcorn Camilan Snack Jagung 300gr',
                'Pia Rasa Kuno isi 10 Bakpia Kacang Hijau, Coklat, Keju Bakpia Kering Kue Pia Oleh Oleh Surabaya',
            ]],
            ['reference' => 'SEED-20260104-001', 'date' => '2026-01-04', 'items' => [
                'Kerupuk Upil Bumbu Rujak / Kerupuk Pasir / Cemilan Kerupuk Pedas Manis Cap Kepala Singa 150 gr',
                'Sambal Bawang Primarasa Khas Surabaya Tradisional 150 gr',
            ]],
            ['reference' => 'SEED-20260202-001', 'date' => '2026-02-02', 'items' => [
                'Popcorn Rasa Caramel Coklat Keju Premium Jagung Popcorn Camilan Snack Jagung 300gr',
                'Pia Rasa Kuno (Kacang Hijau, Coklat, Keju) Bakpia Kering Oleh Oleh Khas Surabaya',
            ]],
            ['reference' => 'SEED-20260203-001', 'date' => '2026-02-03', 'items' => [
                'Popcorn Rasa Caramel Coklat Keju Premium Jagung Popcorn Camilan Snack Jagung 300gr',
                'Kerupuk Upil Bumbu Rujak / Kerupuk Pasir / Cemilan Kerupuk Pedas Manis Cap Kepala Singa 150 gr',
            ]],
            ['reference' => 'SEED-20260301-001', 'date' => '2026-03-01', 'items' => [
                'Popcorn Rasa Original Premium / Jagung Popcorn Camilan Snack Jagung 300gr TERSEDIA GROSIR',
                'Popcorn Rasa Caramel Coklat Keju Premium Jagung Popcorn Camilan Snack Jagung 300gr',
            ]],
            ['reference' => 'SEED-20260303-001', 'date' => '2026-03-03', 'items' => [
                'PROMO BUNDLE POPCORN 3 RASA - Coklat, Caramel, Keju | Camilan Snack Jagung 300gr',
                'Popcorn Rasa Keju (Cheese) Premium / Jagung Popcorn Camilan Snack Jagung 300gr',
            ]],
        ];

        $proposalDataset = [
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

        foreach ($proposalDataset as $reference => $items) {
            $transactions[] = [
                'reference' => 'PROP-20251204-'.$reference,
                'date' => '2025-12-04',
                'items' => $items,
            ];
        }

        foreach ($transactions as $seedTransaction) {
            $transaction = Transaction::query()->firstOrCreate([
                'reference_no' => $seedTransaction['reference'],
            ], [
                'user_id' => $admin->id,
                'transaction_date' => $seedTransaction['date'],
                'status' => 'active',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);

            if ($transaction->details()->count() === 0) {
                $transaction->details()->createMany(array_map(fn (string $item): array => [
                    'snack_id' => $snacks[$item]->id,
                    'quantity' => 1,
                    'status' => 'active',
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ], $seedTransaction['items']));
            }
        }

        if (EclatRun::query()->count() === 0) {
            app(EclatService::class)->analyze(30, 50, [
                'filter_type' => 'date',
                'date' => '2025-12-04',
            ]);
        }
    }

    private function seedUser(string $username, string $name, string $email, string $level): User
    {
        $user = User::query()
            ->where('username', $username)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = new User();
        }

        $user->forceFill([
            'username' => $username,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'level' => $level,
        ])->save();

        return $user;
    }
}
