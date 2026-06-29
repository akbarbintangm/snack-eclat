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
}
