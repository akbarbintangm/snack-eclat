<template>
    <FullPageLoader v-if="analyzing" :label="t('eclatLoading')" />

    <PageHeader eyebrow="Association Rule" :title="t('eclat')" description="Proses ECLAT dijalankan di Laravel agar perhitungan TID List dan DFS intersection tetap cepat saat data bertambah." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" :retry="loadAll" />

    <section class="analysis-grid">
        <form class="tool-panel" @submit.prevent="submitAnalysis">
            <div class="panel-header">
                <p class="eyebrow">Parameter</p>
                <h2>Support & Confidence</h2>
            </div>

            <label class="form-label" for="filterType">{{ t('filterResults') }}</label>
            <select id="filterType" v-model="filterType" class="form-select">
                <option value="all">{{ t('all') }}</option>
                <option value="date">{{ t('date') }}</option>
                <option value="month">{{ t('month') }}</option>
                <option value="year">{{ t('year') }}</option>
            </select>

            <input v-if="filterType === 'date'" v-model="filterDate" class="form-control date-control mt-3" type="date" @click="openNativeDatePicker" @focus="openNativeDatePicker">
            <input v-if="filterType === 'month'" v-model="filterMonth" class="form-control date-control mt-3" type="month" @click="openNativeDatePicker" @focus="openNativeDatePicker">
            <input v-if="filterType === 'year'" v-model="filterYear" class="form-control mt-3" min="2000" max="2100" type="number">

            <button class="btn btn-outline-success w-100 mt-3" type="button" @click="applyFilter">
                {{ t('applyFilter') }}
            </button>

            <label class="form-label mt-4" for="minSupport">{{ t('support') }} <strong>{{ minSupport }}%</strong></label>
            <input id="minSupport" v-model="minSupport" class="form-range" type="range" min="0.1" max="100" step="0.1">

            <label class="form-label mt-4" for="minConfidence">{{ t('confidence') }} <strong>{{ minConfidence }}%</strong></label>
            <input id="minConfidence" v-model="minConfidence" class="form-range" type="range" min="0" max="100">

            <button class="btn btn-success w-100 mt-4" type="submit">{{ t('analyze') }}</button>
        </form>

        <article class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">{{ t('flowchart') }}</p>
                <h2>{{ t('processSteps') }}</h2>
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
                <h2>{{ t('runHistory') }}</h2>
            </div>
            <SkeletonBlock v-if="loading" :lines="5" />
            <div v-else class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>{{ t('period') }}</th>
                            <th>{{ t('support') }}</th>
                            <th>{{ t('confidence') }}</th>
                            <th>{{ t('rule') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="run in runs"
                            :key="run.id"
                            class="clickable-row"
                            :class="{ 'is-active': run.id === activeRunId }"
                            @click="selectRun(run.id)"
                        >
                            <td class="fw-semibold">{{ run.run_code }}</td>
                            <td>{{ formatDisplayDateRange(run.date_from, run.date_to) }}</td>
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
                <p class="eyebrow">{{ t('rules') }}</p>
                <h2>Hasil Association Rule</h2>
            </div>
            <SkeletonBlock v-if="loading" :lines="5" />
            <div v-else class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th>{{ t('rule') }}</th>
                            <th>{{ t('support') }}</th>
                            <th>{{ t('confidence') }}</th>
                            <th>{{ t('lift') }}</th>
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
                <div v-if="results.length === 0" class="empty-state">
                    <p>{{ rulesEmptyMessage }}</p>
                    <button class="btn btn-outline-success" type="button" :disabled="analyzing" @click="runRecommendedAnalysis">
                        {{ t('runRecommendedAnalysis') }}
                    </button>
                </div>
            </div>
            <PaginationBar :meta="resultMeta" @change="loadResults" />
        </article>
    </section>

    <section v-if="latestItemsets.length" class="content-panel mt-3">
        <div class="panel-header">
            <p class="eyebrow">{{ t('frequentItemset') }}</p>
            <h2>{{ t('latestRunItemset') }}</h2>
        </div>
        <div class="tid-list">
            <div v-for="itemset in latestItemsets" :key="itemset.key" class="tid-row">
                <strong>{{ itemset.label }} - {{ itemset.support }}%</strong>
                <span>TID: {{ itemset.tid_list.join(', ') }}</span>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { ApiClientError, type PageMeta } from '../../../shared/api/types';
import { formatDisplayDateRange, openNativeDatePicker } from '../../../shared/dateFormatter';
import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
import FullPageLoader from '../../../shared/components/FullPageLoader.vue';
import PageHeader from '../../../shared/components/PageHeader.vue';
import PaginationBar from '../../../shared/components/PaginationBar.vue';
import SkeletonBlock from '../../../shared/components/SkeletonBlock.vue';
import { t } from '../../../shared/i18n';
import { fetchEclatResults, fetchEclatRun, fetchEclatRuns, runEclatAnalysis } from '../api';
import type { EclatFilterParams, EclatFilterType, EclatItemset, EclatResult, EclatRun, EclatRunDetail, EclatStep } from '../types';

const today = new Date().toISOString().slice(0, 10);
const currentYear = new Date().getFullYear();
const loading = ref(true);
const analyzing = ref(false);
const minSupport = ref(0.1);
const minConfidence = ref(30);
const filterType = ref<EclatFilterType>('all');
const filterDate = ref(today);
const filterMonth = ref(today.slice(0, 7));
const filterYear = ref(currentYear);
const runs = ref<EclatRun[]>([]);
const results = ref<EclatResult[]>([]);
const latestRunDetail = ref<EclatRunDetail | null>(null);
const activeRunId = ref<number | null>(null);
const runMeta = ref<PageMeta | null>(null);
const resultMeta = ref<PageMeta | null>(null);
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

const latestSteps = computed<EclatStep[]>(() => latestRunDetail.value?.steps ?? []);
const latestItemsets = computed<EclatItemset[]>(() => latestRunDetail.value?.run.frequent_itemsets ?? []);
const rulesEmptyMessage = computed(() => {
    const run = latestRunDetail.value?.run;

    if (run && run.frequent_itemset_count === 0) {
        return t('emptyRulesNoItemset');
    }

    if (run && (Number(run.min_support) >= 30 || Number(run.min_confidence) >= 80)) {
        return t('emptyRulesHighThreshold');
    }

    return t('emptyRules');
});
const periodParams = computed<EclatFilterParams>(() => {
    if (filterType.value === 'date') {
        return { filter_type: 'date', date: filterDate.value };
    }

    if (filterType.value === 'month') {
        return { filter_type: 'month', month: filterMonth.value };
    }

    if (filterType.value === 'year') {
        return { filter_type: 'year', year: Number(filterYear.value) };
    }

    return { filter_type: 'all' };
});

async function loadRuns(page = runMeta.value?.current_page ?? 1): Promise<void> {
    const response = await fetchEclatRuns({ page, per_page: 5, ...periodParams.value });
    runs.value = response.data;
    runMeta.value = response.meta;
}

async function loadResults(page = resultMeta.value?.current_page ?? 1): Promise<void> {
    const response = await fetchEclatResults({
        page,
        per_page: 10,
        ...periodParams.value,
        run_id: activeRunId.value ?? undefined,
    });
    results.value = response.data;
    resultMeta.value = response.meta;
}

async function loadAll(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        await loadRuns(1);
        activeRunId.value = runs.value[0]?.id ?? null;
        if (runs.value[0]) {
            const response = await fetchEclatRun(runs.value[0].id);
            latestRunDetail.value = response.data;
        } else {
            latestRunDetail.value = null;
        }
        await loadResults(1);
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
        const response = await runEclatAnalysis(Number(minSupport.value), Number(minConfidence.value), periodParams.value);
        latestRunDetail.value = response.data;
        activeRunId.value = response.data.run.id;
        await loadRuns(1);
        await loadResults(1);
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal menjalankan ECLAT';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        analyzing.value = false;
    }
}

async function applyFilter(): Promise<void> {
    await loadAll();
}

async function selectRun(runId: number): Promise<void> {
    activeRunId.value = runId;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await fetchEclatRun(runId);
        latestRunDetail.value = response.data;
        await loadResults(1);
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat ECLAT';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    }
}

async function runRecommendedAnalysis(): Promise<void> {
    minSupport.value = 0.1;
    minConfidence.value = 30;
    await submitAnalysis();
}

onMounted(loadAll);
</script>
