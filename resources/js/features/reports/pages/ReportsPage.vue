<template>
    <PageHeader eyebrow="Output" :title="t('reports')" description="Rekomendasi penjualan dari association rule yang sudah melewati minimum support dan confidence." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" :retry="loadRecommendations" />

    <section class="content-panel">
        <SkeletonBlock v-if="loading" :lines="7" />
        <div v-else class="table-responsive">
            <table class="table align-middle data-table">
                <thead>
                    <tr>
                        <th>Rule</th>
                        <th>Support</th>
                        <th>Confidence</th>
                        <th>Rekomendasi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="recommendation in recommendations" :key="recommendation.id">
                        <td class="fw-semibold">{{ recommendation.combination_item }}</td>
                        <td>{{ recommendation.support }}%</td>
                        <td>{{ recommendation.confidence }}%</td>
                        <td>{{ recommendation.recommendation }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="recommendations.length === 0" class="empty-state">{{ t('noData') }}</p>
        </div>
    </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { ApiClientError } from '../../../shared/api/types';
import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
import PageHeader from '../../../shared/components/PageHeader.vue';
import SkeletonBlock from '../../../shared/components/SkeletonBlock.vue';
import { t } from '../../../shared/i18n';
import { fetchRecommendations } from '../api';
import type { Recommendation } from '../types';

const loading = ref(true);
const recommendations = ref<Recommendation[]>([]);
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

async function loadRecommendations(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await fetchRecommendations(10);
        recommendations.value = response.data;
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat laporan';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loading.value = false;
    }
}

onMounted(loadRecommendations);
</script>
