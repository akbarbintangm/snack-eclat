<template>
    <PageHeader
        eyebrow="Overview"
        title="Snack Eclat"
        description="Monitoring data transaksi, hasil ECLAT, dan rekomendasi pola penjualan snack."
    />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" :retry="loadSummary" />

    <section class="metric-grid">
        <article v-for="metric in metrics" :key="metric.label" class="metric-card">
            <SkeletonBlock v-if="loading" :lines="2" />
            <template v-else>
                <span class="metric-label">{{ metric.label }}</span>
                <strong>{{ metric.value }}</strong>
                <span>{{ metric.note }}</span>
            </template>
        </article>
    </section>

    <section class="dashboard-layout">
        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Flow ECLAT</p>
                <h2>Informasi Proses</h2>
            </div>
            <SkeletonBlock v-if="loading" :lines="5" />
            <ol v-else class="step-list">
                <li v-for="step in summary?.process_steps ?? []" :key="step.order">
                    <span>{{ step.order }}</span>
                    <div>
                        <strong>{{ step.name }}</strong>
                        <p>{{ step.description }}</p>
                        <small>{{ step.metric }}</small>
                    </div>
                </li>
            </ol>
        </article>

        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Top Rule</p>
                <h2>Rekomendasi Teratas</h2>
            </div>
            <SkeletonBlock v-if="loading" :lines="4" />
            <div v-else-if="summary?.top_rule" class="rule-highlight">
                <strong>{{ summary.top_rule.combination_item }}</strong>
                <span>Support {{ summary.top_rule.support }}% · Confidence {{ summary.top_rule.confidence }}%</span>
                <p>{{ summary.top_rule.recommendation }}</p>
            </div>
            <p v-else class="empty-state">{{ t('noData') }}</p>
        </article>
    </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { ApiClientError } from '../../../shared/api/types';
import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
import PageHeader from '../../../shared/components/PageHeader.vue';
import SkeletonBlock from '../../../shared/components/SkeletonBlock.vue';
import { t } from '../../../shared/i18n';
import { fetchDashboardSummary } from '../api';
import type { DashboardSummary } from '../types';

const loading = ref(true);
const summary = ref<DashboardSummary | null>(null);
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

const metrics = computed(() => [
    { label: t('snacks'), value: summary.value?.snack_count ?? '-', note: 'Master produk aktif' },
    { label: t('transactions'), value: summary.value?.transaction_count ?? '-', note: 'Transaksi siap dianalisis' },
    { label: 'Item', value: summary.value?.transaction_item_count ?? '-', note: 'Detail snack dalam transaksi' },
    { label: 'Run ECLAT', value: summary.value?.analysis_run_count ?? '-', note: 'Riwayat analisis tersimpan' },
]);

async function loadSummary(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await fetchDashboardSummary();
        summary.value = response.data;
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat dashboard';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loading.value = false;
    }
}

onMounted(loadSummary);
</script>
