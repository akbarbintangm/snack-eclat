<template>
    <FullPageLoader v-if="analyzing" label="Menjalankan analisis ECLAT..." />

    <PageHeader eyebrow="Association Rule" :title="t('eclat')" description="Proses ECLAT dijalankan di Laravel agar perhitungan TID List dan DFS intersection tetap cepat saat data bertambah." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" :retry="loadAll" />

    <section class="analysis-grid">
        <form class="tool-panel" @submit.prevent="submitAnalysis">
            <div class="panel-header">
                <p class="eyebrow">Parameter</p>
                <h2>Support & Confidence</h2>
            </div>

            <label class="form-label" for="minSupport">Minimum Support <strong>{{ minSupport }}%</strong></label>
            <input id="minSupport" v-model="minSupport" class="form-range" type="range" min="0" max="100">

            <label class="form-label mt-4" for="minConfidence">Minimum Confidence <strong>{{ minConfidence }}%</strong></label>
            <input id="minConfidence" v-model="minConfidence" class="form-range" type="range" min="0" max="100">

            <button class="btn btn-success w-100 mt-4" type="submit">{{ t('analyze') }}</button>
        </form>

        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Flowchart</p>
                <h2>Langkah Proses</h2>
            </div>
            <SkeletonBlock v-if="loading" :lines="5" />
            <ol v-else class="step-list">
                <li v-for="step in latestSteps" :key="step.order">
                    <span>{{ step.order }}</span>
                    <div>
                        <strong>{{ step.name }}</strong>
                        <p>{{ step.description }}</p>
                        <small>{{ step.metric }}</small>
                    </div>
                </li>
            </ol>
        </article>
    </section>

    <section class="table-grid">
        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Run History</p>
                <h2>Riwayat Analisis</h2>
            </div>
            <SkeletonBlock v-if="loading" :lines="5" />
            <div v-else class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Support</th>
                            <th>Confidence</th>
                            <th>Rule</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="run in runs" :key="run.id">
                            <td class="fw-semibold">{{ run.run_code }}</td>
                            <td>{{ run.min_support }}%</td>
                            <td>{{ run.min_confidence }}%</td>
                            <td>{{ run.rule_count }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <PaginationBar :meta="runMeta" @change="loadRuns" />
        </article>

        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Rules</p>
                <h2>Hasil Association Rule</h2>
            </div>
            <SkeletonBlock v-if="loading" :lines="5" />
            <div v-else class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th>Rule</th>
                            <th>Support</th>
                            <th>Confidence</th>
                            <th>Lift</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="result in results" :key="result.id">
                            <td>{{ result.combination_item }}</td>
                            <td>{{ result.support }}%</td>
                            <td class="fw-semibold">{{ result.confidence }}%</td>
                            <td>{{ result.lift_ratio ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="results.length === 0" class="empty-state">{{ t('noData') }}</p>
            </div>
            <PaginationBar :meta="resultMeta" @change="loadResults" />
        </article>
    </section>

    <section v-if="latestItemsets.length" class="content-panel mt-3">
        <div class="panel-header">
            <p class="eyebrow">Frequent Itemset</p>
            <h2>Itemset dari Run Terbaru</h2>
        </div>
        <div class="tid-list">
            <div v-for="itemset in latestItemsets" :key="itemset.key" class="tid-row">
                <strong>{{ itemset.label }} · {{ itemset.support }}%</strong>
                <span>TID: {{ itemset.tid_list.join(', ') }}</span>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { ApiClientError, type PageMeta } from '../../../shared/api/types';
import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
import FullPageLoader from '../../../shared/components/FullPageLoader.vue';
import PageHeader from '../../../shared/components/PageHeader.vue';
import PaginationBar from '../../../shared/components/PaginationBar.vue';
import SkeletonBlock from '../../../shared/components/SkeletonBlock.vue';
import { t } from '../../../shared/i18n';
import { fetchEclatResults, fetchEclatRun, fetchEclatRuns, runEclatAnalysis } from '../api';
import type { EclatItemset, EclatResult, EclatRun, EclatRunDetail, EclatStep } from '../types';

const loading = ref(true);
const analyzing = ref(false);
const minSupport = ref(30);
const minConfidence = ref(50);
const runs = ref<EclatRun[]>([]);
const results = ref<EclatResult[]>([]);
const latestRunDetail = ref<EclatRunDetail | null>(null);
const runMeta = ref<PageMeta | null>(null);
const resultMeta = ref<PageMeta | null>(null);
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

const latestSteps = computed<EclatStep[]>(() => latestRunDetail.value?.steps ?? []);
const latestItemsets = computed<EclatItemset[]>(() => latestRunDetail.value?.run.frequent_itemsets ?? []);

async function loadRuns(page = runMeta.value?.current_page ?? 1): Promise<void> {
    const response = await fetchEclatRuns({ page, per_page: 5 });
    runs.value = response.data;
    runMeta.value = response.meta;
}

async function loadResults(page = resultMeta.value?.current_page ?? 1): Promise<void> {
    const response = await fetchEclatResults({ page, per_page: 10 });
    results.value = response.data;
    resultMeta.value = response.meta;
}

async function loadAll(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        await Promise.all([loadRuns(1), loadResults(1)]);
        if (runs.value[0]) {
            const response = await fetchEclatRun(runs.value[0].id);
            latestRunDetail.value = response.data;
        }
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat ECLAT';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loading.value = false;
    }
}

async function submitAnalysis(): Promise<void> {
    analyzing.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await runEclatAnalysis(Number(minSupport.value), Number(minConfidence.value));
        latestRunDetail.value = response.data;
        await Promise.all([loadRuns(1), loadResults(1)]);
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal menjalankan ECLAT';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        analyzing.value = false;
    }
}

onMounted(loadAll);
</script>
