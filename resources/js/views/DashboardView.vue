<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { buildEclatSummary, formatPercent, reportThresholds } from '../data/snackEclat';

const summary = computed(() => buildEclatSummary());
const strongestPair = computed(() => summary.value.pairItemsets[0]);
</script>

<template>
    <section class="page-heading">
        <div>
            <p class="eyebrow">Laravel 12 + Vue 3 + Bootstrap 5</p>
            <h1>Dashboard Analisis Pola Penjualan Snack</h1>
            <p class="lead-text">
                Aplikasi monolith Laravel-Vue untuk mengolah transaksi snack menjadi frequent itemset
                dan association rule dengan algoritma ECLAT.
            </p>
        </div>
        <RouterLink class="btn btn-success" to="/analisis">Buka Analisis</RouterLink>
    </section>

    <section class="metric-grid" aria-label="Ringkasan analisis">
        <article class="metric-card">
            <span class="metric-label">Transaksi Contoh</span>
            <strong>{{ summary.totalTransactions }}</strong>
            <span>Data T1 sampai T25 dari laporan skripsi</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Produk Snack</span>
            <strong>{{ summary.products.length }}</strong>
            <span>Pop U Corn dan Pia Kuno</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Minimum Support</span>
            <strong>{{ reportThresholds.minSupport }}%</strong>
            <span>Ambang frequent itemset</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Minimum Confidence</span>
            <strong>{{ reportThresholds.minConfidence }}%</strong>
            <span>Ambang association rule</span>
        </article>
    </section>

    <section class="dashboard-layout">
        <div class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Hasil baseline skripsi</p>
                <h2>Kombinasi paling kuat</h2>
            </div>
            <div v-if="strongestPair" class="result-highlight">
                <span>{{ strongestPair.label }}</span>
                <strong>{{ formatPercent(strongestPair.support) }}</strong>
            </div>
            <p class="body-copy">
                Kombinasi Pop U Corn OG dan Pop U Corn Cheese menjadi pasangan dengan support
                tertinggi. Pola ini dapat dipakai sebagai dasar penataan rak, paket promosi, dan
                rekomendasi cross-selling.
            </p>
        </div>

        <div class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Association rule</p>
                <h2>Rekomendasi aktif</h2>
            </div>
            <ul class="rule-list">
                <li v-for="rule in summary.rules" :key="rule.key">
                    <span>{{ rule.label }}</span>
                    <strong>{{ formatPercent(rule.confidence) }}</strong>
                </li>
            </ul>
        </div>
    </section>
</template>
