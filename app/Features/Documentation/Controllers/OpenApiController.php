<?php

namespace App\Features\Documentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class OpenApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/documentation/openapi.json",
     *     tags={"Documentation"},
     *     summary="OpenAPI JSON untuk Swagger UI",
     *     @OA\Response(response=200, description="OpenAPI specification")
     * )
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Snack Eclat API',
                'version' => '0.2.0',
                'description' => 'API Laravel untuk Snack, Transaksi, ECLAT, dan Laporan. Security scheme disiapkan sebagai Bearer JWT.',
            ],
            'servers' => [
                ['url' => url('/api/v1'), 'description' => 'Local API v1'],
            ],
            'security' => [
                ['bearerAuth' => []],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
            ],
            'tags' => [
                ['name' => 'Snacks'],
                ['name' => 'Transactions'],
                ['name' => 'ECLAT'],
                ['name' => 'Reports'],
                ['name' => 'Documentation'],
            ],
            'paths' => $this->paths(),
        ]);
    }

    private function paths(): array
    {
        return [
            '/snacks' => [
                'get' => $this->operation('Snacks', 'List snack', 'Mengambil data snack dengan pagination.'),
                'post' => $this->operation('Snacks', 'Tambah snack', 'Membuat master snack baru.', 201),
            ],
            '/snacks/{snack}' => [
                'get' => $this->operation('Snacks', 'Detail snack', 'Mengambil detail snack.'),
                'put' => $this->operation('Snacks', 'Update snack', 'Mengubah data snack.'),
                'delete' => $this->operation('Snacks', 'Hapus snack', 'Menghapus snack secara soft delete.'),
            ],
            '/transactions' => [
                'get' => $this->operation('Transactions', 'List transaksi', 'Mengambil transaksi dengan detail item dan pagination.'),
                'post' => $this->operation('Transactions', 'Tambah transaksi', 'Membuat transaksi beserta detail snack.', 201),
            ],
            '/transactions/{transaction}' => [
                'get' => $this->operation('Transactions', 'Detail transaksi', 'Mengambil transaksi beserta detail snack.'),
                'put' => $this->operation('Transactions', 'Update transaksi', 'Mengubah transaksi dan item snack.'),
                'delete' => $this->operation('Transactions', 'Hapus transaksi', 'Menghapus transaksi secara soft delete.'),
            ],
            '/eclat/runs' => [
                'get' => $this->operation('ECLAT', 'Riwayat ECLAT', 'Mengambil riwayat proses ECLAT.'),
            ],
            '/eclat/analyze' => [
                'post' => $this->operation('ECLAT', 'Jalankan analisis ECLAT', 'Memproses TID List, frequent itemset, dan association rule.', 201),
            ],
            '/eclat/runs/{run}' => [
                'get' => $this->operation('ECLAT', 'Detail run ECLAT', 'Mengambil detail proses, step, itemset, dan rule.'),
            ],
            '/eclat/results' => [
                'get' => $this->operation('ECLAT', 'Hasil ECLAT', 'Mengambil hasil association rule dengan pagination.'),
            ],
            '/reports/summary' => [
                'get' => $this->operation('Reports', 'Ringkasan dashboard', 'Mengambil ringkasan data untuk dashboard.'),
            ],
            '/reports/recommendations' => [
                'get' => $this->operation('Reports', 'Rekomendasi penjualan', 'Mengambil rekomendasi rule ECLAT teratas.'),
            ],
            '/documentation/openapi.json' => [
                'get' => $this->operation('Documentation', 'OpenAPI JSON', 'Spesifikasi OpenAPI untuk Swagger UI.'),
            ],
        ];
    }

    private function operation(string $tag, string $summary, string $description, int $successCode = 200): array
    {
        return [
            'tags' => [$tag],
            'summary' => $summary,
            'description' => $description,
            'security' => [['bearerAuth' => []]],
            'responses' => [
                (string) $successCode => ['description' => 'Success'],
                '401' => ['description' => 'Unauthenticated bearer token'],
                '422' => ['description' => 'Validation error'],
                '500' => ['description' => 'Server error'],
            ],
        ];
    }
}
