# Snack Eclat

Snack Eclat adalah aplikasi web monolith Laravel 12 dan Vue 3 untuk menganalisis pola penjualan snack menggunakan algoritma Equivalence Class Transformation (ECLAT). Aplikasi ini disiapkan sebagai dasar implementasi sistem dari laporan skripsi **"Implementasi Algoritma Equivalence Class Transformation (ECLAT) untuk Menentukan Pola Penjualan Snack"** oleh Jamilatun Nasichah.

## Gambaran Aplikasi

Data transaksi penjualan snack sering hanya dipakai sebagai catatan harian. Snack Eclat mengolah data tersebut menjadi informasi pola pembelian, sehingga pemilik toko dapat melihat produk yang sering dibeli bersamaan dan menjadikannya dasar keputusan penjualan.

Manfaat utama aplikasi:

- Membantu pengelolaan stok berdasarkan pola transaksi.
- Menemukan kombinasi produk snack yang sering muncul bersamaan.
- Mendukung strategi penataan produk, paket promosi, cross-selling, dan upselling.
- Mengurangi keputusan berbasis perkiraan dengan analisis data transaksi.

## Dasar Penelitian

Berdasarkan laporan skripsi, studi kasus berfokus pada Toko ABC dengan data penjualan snack selama 6 bulan. Ruang lingkup penelitian dibatasi pada kategori snack dan proses analisis menggunakan algoritma ECLAT.

Algoritma ECLAT bekerja dengan format data vertikal menggunakan Transaction ID List (TID List). Setiap item menyimpan daftar transaksi yang memuat item tersebut, lalu kombinasi item dicari melalui irisan TID List. Dari proses tersebut sistem menghasilkan:

- Frequent itemset berdasarkan minimum support.
- Association rule berdasarkan minimum confidence.
- Rekomendasi kombinasi snack yang dapat digunakan untuk strategi penjualan.

Contoh baseline dari laporan:

- Minimum support: 30%.
- Minimum confidence: 50%.
- Kombinasi terkuat: Pop U Corn OG dan Pop U Corn Cheese.
- Support kombinasi: 32%.
- Confidence rule: 53,33%.

## Kebutuhan Fungsional

Fitur inti yang direncanakan pada sistem:

- Input data master snack.
- Input data transaksi penjualan snack.
- Transformasi data transaksi horizontal menjadi TID List.
- Pencarian frequent itemset.
- Perhitungan support dan confidence.
- Pembentukan association rule.
- Penyajian laporan hasil analisis pola penjualan.

## Pengguna Sistem

- **Admin**: mengelola data snack, data transaksi, dan menjalankan proses analisis ECLAT.
- **Pemilik Toko**: melihat laporan pola penjualan, hasil association rule, dan rekomendasi strategi penjualan.

## Struktur Data Rencana

Berdasarkan rancangan CDM/PDM pada laporan, entitas utama sistem adalah:

- `snack`: menyimpan master produk snack.
- `transaksi`: menyimpan data transaksi penjualan.
- `detail_transaksi`: menyimpan item snack pada setiap transaksi.
- `hasil_eclat`: menyimpan kombinasi item, support, dan confidence dari hasil analisis.

## Teknologi

- Laravel 12
- PHP 8.2 atau lebih baru
- Vue 3
- Vue Router 4
- Bootstrap 5
- Vite
- MySQL

Frontend dibuat sebagai SPA di dalam Laravel. Ini bukan pemisahan backend Laravel dan frontend Vue menjadi dua project, melainkan Vue berjalan sebagai bagian dari aplikasi Laravel yang sama.

## Konfigurasi Database

Konfigurasi default project:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=snack_eclat
DB_USERNAME=root
DB_PASSWORD=
```

Database yang dipakai adalah MySQL lokal pada port `3306` dengan user `root` tanpa password.

## Cara Menjalankan

Install dependency PHP:

```bash
composer install
```

Install dependency Node:

```bash
npm install
```

Salin file environment bila belum ada:

```bash
copy .env.example .env
php artisan key:generate
```

Jalankan migration:

```bash
php artisan migrate
```

Jalankan Vite:

```bash
npm run dev
```

Jalankan Laravel di port 9000:

```bash
php artisan serve --port=9000
```

Aplikasi dapat dibuka di:

- `http://127.0.0.1:9000`
- `http://localhost:9000`

Untuk Laragon, aplikasi juga dapat diarahkan ke `localhost:8282` sesuai konfigurasi lokal yang dipakai.

## Mode SPA

Routing Laravel mengarah ke satu Blade shell, lalu navigasi halaman ditangani oleh Vue Router. Rute seperti `/transaksi`, `/analisis`, dan `/dokumentasi` tetap masuk ke aplikasi Vue tanpa refresh penuh saat berpindah halaman dari dalam aplikasi.

## Status Saat Ini

Project sudah berisi setup awal Laravel-Vue:

- Vue 3 terpasang dan termount melalui Vite.
- Bootstrap 5 terpasang dan dipakai sebagai dasar UI.
- Vue Router aktif dengan history mode.
- Dashboard, data transaksi contoh, simulasi ECLAT, dan dokumentasi ringkas sudah tersedia sebagai SPA.
- Database MySQL lokal `snack_eclat` sudah menjadi konfigurasi utama pada `.env` dan `.env.example`.
