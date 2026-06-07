<template>
    <section class="page-heading">
        <div>
            <p class="eyebrow">Dokumentasi produk</p>
            <h1>Ringkasan Sistem Snack Eclat</h1>
            <p class="lead-text">
                Sistem dirancang untuk membantu pemilik toko memahami kombinasi snack yang sering
                dibeli bersamaan berdasarkan data transaksi.
            </p>
        </div>
    </section>

    <section class="doc-grid">
        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Tujuan</p>
                <h2>Masalah yang Diselesaikan</h2>
            </div>
            <p class="body-copy">
                Data penjualan Toko ABC sebelumnya hanya menjadi catatan transaksi. Snack Eclat
                mengubah data tersebut menjadi informasi pola pembelian untuk mendukung pengelolaan
                stok, penataan produk, paket promosi, cross-selling, dan upselling.
            </p>
        </article>

        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Pengguna</p>
                <h2>Peran Sistem</h2>
            </div>
            <ul class="plain-list">
                <li>Admin mengelola data snack, transaksi penjualan, dan proses analisis ECLAT.</li>
                <li>Pemilik toko membaca laporan pola penjualan dan rekomendasi association rule.</li>
            </ul>
        </article>

        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Fitur inti</p>
                <h2>Kebutuhan Fungsional</h2>
            </div>
            <ul class="plain-list">
                <li>Input data transaksi penjualan snack.</li>
                <li>Transformasi transaksi horizontal menjadi TID List.</li>
                <li>Pencarian frequent itemset berdasarkan minimum support.</li>
                <li>Perhitungan confidence dan pembentukan association rule.</li>
                <li>Penyajian laporan hasil analisis pola penjualan.</li>
            </ul>
        </article>

        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Database</p>
                <h2>Entitas Utama</h2>
            </div>
            <ul class="plain-list">
                <li>Snack untuk menyimpan master produk.</li>
                <li>Transaksi untuk menyimpan data penjualan.</li>
                <li>Detail_Transaksi untuk menyimpan item pada setiap transaksi.</li>
                <li>Hasil_ECLAT untuk menyimpan kombinasi item, support, dan confidence.</li>
            </ul>
        </article>
    </section>
</template>
