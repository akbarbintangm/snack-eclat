# Snack Eclat

Versi dokumentasi: **0.2.0 / Feature-002**

Snack Eclat adalah aplikasi monolith Laravel 12 + Vue 3 untuk analisis pola penjualan snack menggunakan algoritma Equivalence Class Transformation (ECLAT). Aplikasi ini dibuat berdasarkan laporan skripsi **"Implementasi Algoritma Equivalence Class Transformation (ECLAT) untuk Menentukan Pola Penjualan Snack"** oleh Jamilatun Nasichah.

## Ringkasan Indonesia

### Tujuan

Snack Eclat membantu Toko ABC mengubah data transaksi penjualan snack menjadi informasi pola pembelian. Hasil analisis dapat digunakan untuk pengelolaan stok, penataan produk, paket promosi, cross-selling, dan upselling.

### Fitur

- Master data snack.
- Entry transaksi penjualan dan detail item snack.
- Analisis ECLAT berbasis Laravel dengan vertical TID List dan DFS intersection.
- Penyimpanan riwayat proses ECLAT.
- Penyimpanan hasil association rule pada tabel `hasil_eclat`.
- Dashboard ringkasan.
- Laporan rekomendasi penjualan.
- API JSON dengan pagination, status code error, dan logging.
- Swagger UI di `/docs` dengan Bearer Auth JWT security scheme.
- UI Vue modular dengan TypeScript, skeleton loading, full-page loading, light/dark theme, dan translasi ID/EN.

### Database

Database default:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=snack_eclat
DB_USERNAME=root
DB_PASSWORD=
```

Tabel fitur:

- `snacks`
- `transactions`
- `transaction_details`
- `eclat_runs`
- `hasil_eclat`

Setiap tabel fitur memakai primary key `id` increment, kolom `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_by`, dan `deleted_at`.

### Optimasi

- Index pada status, nama snack, tanggal transaksi, foreign key detail transaksi, support, confidence, dan run ECLAT.
- Endpoint list memakai pagination.
- Relasi transaksi memakai eager loading untuk menghindari query berulang.
- Proses ECLAT dijalankan di Laravel agar dekat dengan database.
- Hasil analisis disimpan agar data GET berikutnya cepat tanpa menghitung ulang.
- Vue route memakai lazy loading, termasuk Swagger UI sebagai chunk terpisah.

### Alur ECLAT

1. Mengambil transaksi aktif.
2. Membersihkan item kosong dan duplikasi item dalam satu transaksi.
3. Mengubah transaksi horizontal menjadi TID List.
4. Menghitung support setiap itemset.
5. Menjalankan DFS intersection untuk kombinasi k-itemset.
6. Menghitung confidence dan lift ratio.
7. Menyimpan association rule valid sebagai rekomendasi.

Baseline dari laporan skripsi:

- Minimum support: 30%.
- Minimum confidence: 50%.
- Rule terkuat: Pop U Corn Cheese -> Pop U Corn OG dan Pop U Corn OG -> Pop U Corn Cheese.
- Support: 32%.
- Confidence: 53,33%.

### Struktur Backend

Setiap modul utama memiliki Controller, Model, Service, Interface, dan Repository:

- `app/Features/Snacks`
- `app/Features/Transactions`
- `app/Features/Eclat`
- `app/Features/Reports`
- `app/Features/Documentation`

### Struktur Frontend

Vue dipisah per modul:

- `resources/js/features/dashboard`
- `resources/js/features/snacks`
- `resources/js/features/transactions`
- `resources/js/features/eclat`
- `resources/js/features/reports`
- `resources/js/features/documentation`
- `resources/js/shared`

Setiap modul fitur memiliki file `.vue` untuk UI dan `.ts` untuk proses data/API.

### API Utama

- `GET /api/v1/snacks`
- `POST /api/v1/snacks`
- `GET /api/v1/transactions`
- `POST /api/v1/transactions`
- `GET /api/v1/eclat/runs`
- `POST /api/v1/eclat/analyze`
- `GET /api/v1/eclat/results`
- `GET /api/v1/reports/summary`
- `GET /api/v1/reports/recommendations`
- `GET /api/v1/documentation/openapi.json`

### Cara Menjalankan

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve --port=9000
```

Akses aplikasi:

- `http://127.0.0.1:9000`
- `http://localhost:9000`

Swagger UI:

- `http://127.0.0.1:9000/docs`

## English Summary

### Purpose

Snack Eclat helps Toko ABC turn snack sales transactions into buying-pattern insights. The analysis output supports stock planning, product placement, promotional bundles, cross-selling, and upselling.

### Features

- Snack master data.
- Sales transaction entry with snack item details.
- Laravel-side ECLAT analysis using vertical TID List and DFS intersection.
- ECLAT run history.
- Association rule storage in `hasil_eclat`.
- Dashboard summary.
- Recommendation report.
- JSON API with pagination, error status code handling, and logging.
- Swagger UI at `/docs` with Bearer Auth JWT security scheme.
- Modular Vue UI with TypeScript, skeleton loading, full-page loading, light/dark theme, and ID/EN translations.

### Database

Default database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=snack_eclat
DB_USERNAME=root
DB_PASSWORD=
```

Feature tables:

- `snacks`
- `transactions`
- `transaction_details`
- `eclat_runs`
- `hasil_eclat`

Each feature table uses an incrementing `id` primary key plus `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_by`, and `deleted_at`.

### Optimization

- Indexes are added for status, snack name, transaction date, transaction-detail foreign keys, support, confidence, and ECLAT run fields.
- List endpoints use pagination.
- Transaction relations use eager loading.
- ECLAT runs in Laravel to keep processing close to the database.
- Analysis results are persisted so subsequent GET requests do not recalculate rules.
- Vue routes are lazy-loaded, with Swagger UI split into a separate chunk.

### ECLAT Flow

1. Load active transactions.
2. Clean empty items and duplicate items within each transaction.
3. Transform horizontal transaction data into vertical TID List.
4. Calculate itemset support.
5. Run DFS intersection for k-itemset candidates.
6. Calculate confidence and lift ratio.
7. Store valid association rules as recommendations.

Thesis baseline:

- Minimum support: 30%.
- Minimum confidence: 50%.
- Strongest rules: Pop U Corn Cheese -> Pop U Corn OG and Pop U Corn OG -> Pop U Corn Cheese.
- Support: 32%.
- Confidence: 53.33%.

### Run Locally

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve --port=9000
```

App URL:

- `http://127.0.0.1:9000`
- `http://localhost:9000`

Swagger UI:

- `http://127.0.0.1:9000/docs`
