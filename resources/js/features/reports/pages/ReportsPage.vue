<template>
    <PageHeader eyebrow="Output" :title="t('reports')" description="Rekomendasi penjualan dari association rule yang sudah melewati minimum support dan confidence." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" :retry="loadRecommendations" />

    <section class="content-panel">
        <div class="table-toolbar">
            <input v-model="search" class="form-control" :placeholder="t('searchRecommendation')" @keyup.enter="loadRecommendations(1)">
            <button class="btn btn-outline-success" type="button" @click="loadRecommendations(1)">{{ t('search') }}</button>
        </div>
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
        <PaginationBar :meta="meta" @change="loadRecommendations" />
    </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { ApiClientError, type PageMeta } from '../../../shared/api/types';
import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
import PageHeader from '../../../shared/components/PageHeader.vue';
import PaginationBar from '../../../shared/components/PaginationBar.vue';
import SkeletonBlock from '../../../shared/components/SkeletonBlock.vue';
import { t } from '../../../shared/i18n';
import { fetchRecommendations } from '../api';
import type { Recommendation } from '../types';

const loading = ref(true);
const recommendations = ref<Recommendation[]>([]);
const meta = ref<PageMeta | null>(null);
const search = ref('');
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

async function loadRecommendations(page = meta.value?.current_page ?? 1): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await fetchRecommendations({
            page,
            per_page: 10,
            search: search.value.trim() || undefined,
        });
        recommendations.value = response.data;
        meta.value = response.meta;
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat laporan';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loading.value = false;
    }
}

onMounted(loadRecommendations);
</script>
