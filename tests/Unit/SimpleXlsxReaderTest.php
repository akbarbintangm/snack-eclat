<?php

namespace Tests\Unit;

use App\Support\Excel\SimpleXlsxReader;
use PHPUnit\Framework\TestCase;
use Tests\Concerns\CreatesXlsxWorkbook;

class SimpleXlsxReaderTest extends TestCase
{
    use CreatesXlsxWorkbook;

    public function test_it_reads_transaction_rows_from_xlsx_workbook(): void
    {
        $path = $this->createXlsxWorkbook([
            ['Waktu Pesanan Dibuat', 'Waktu Pembayaran Dilakukan', 'Metode Pembayaran', 'Nama Produk', 'Harga Awal', 'Harga Setelah Diskon', 'Jumlah', 'Subtotal Pesanan', 'Username (Pembeli)', 'Kota/Kabupaten', 'Provinsi', 'Waktu Pesanan Selesai'],
            ['2025-10-02 10:29', '2025-10-02 10:35', 'Saldo ShopeePay', 'PROMO BUNDLE POPCORN 3 RASA | Camilan Snack Jagung 300gr', '140.000', '119.000', '1', '119.000', 'aniki1989', 'KAB. GRESIK', 'JAWA TIMUR', '2025-10-03 15:23'],
        ]);

        $rows = (new SimpleXlsxReader())->read($path);

        $this->assertCount(1, $rows);
        $this->assertSame('Oktober 2025', $rows[0]['sheet']);
        $this->assertSame('PROMO BUNDLE POPCORN 3 RASA | Camilan Snack Jagung 300gr', $rows[0]['values']['nama_produk']);
        $this->assertSame('aniki1989', $rows[0]['values']['username_pembeli']);
    }

    public function test_it_reads_workbooks_with_absolute_worksheet_relationship_targets(): void
    {
        $path = $this->createXlsxWorkbook([
            ['Waktu Pesanan Dibuat', 'Nama Produk', 'Jumlah'],
            ['2025-12-01 10:00', 'Popcorn Original', '2'],
        ], absoluteWorksheetTarget: true);

        $rows = (new SimpleXlsxReader())->read($path);

        $this->assertCount(1, $rows);
        $this->assertSame('Popcorn Original', $rows[0]['values']['nama_produk']);
        $this->assertSame('2', $rows[0]['values']['jumlah']);
    }

    public function test_it_reads_shared_strings_without_count_attributes(): void
    {
        $path = $this->createXlsxWorkbook([
            ['Waktu Pesanan Dibuat', 'Nama Produk', 'Jumlah'],
            ['2025-12-01 10:00', 'Pia Kuno', '1'],
        ], omitSharedStringCountAttributes: true);

        $rows = (new SimpleXlsxReader())->read($path);

        $this->assertCount(1, $rows);
        $this->assertSame('Pia Kuno', $rows[0]['values']['nama_produk']);
    }
}
