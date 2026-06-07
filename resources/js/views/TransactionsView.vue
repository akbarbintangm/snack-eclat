<script setup>
import { transactions } from '../data/snackEclat';
</script>

<template>
    <section class="page-heading">
        <div>
            <p class="eyebrow">Dataset transaksi</p>
            <h1>Data Penjualan Snack</h1>
            <p class="lead-text">
                Sampel transaksi dari laporan skripsi dipakai sebagai data awal untuk memvalidasi
                proses transformasi vertikal ECLAT.
            </p>
        </div>
    </section>

    <section class="content-panel">
        <div class="table-responsive">
            <table class="table align-middle data-table">
                <thead>
                    <tr>
                        <th scope="col">ID Transaksi</th>
                        <th scope="col">Item Snack</th>
                        <th scope="col" class="text-end">Jumlah Item</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="transaction in transactions" :key="transaction.id">
                        <td class="fw-semibold">{{ transaction.id }}</td>
                        <td>
                            <span
                                v-for="item in transaction.items"
                                :key="`${transaction.id}-${item}`"
                                class="item-badge"
                            >
                                {{ item }}
                            </span>
                        </td>
                        <td class="text-end">{{ transaction.items.length }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
