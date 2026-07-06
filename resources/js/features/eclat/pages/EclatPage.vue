<template>
    <FullPageLoader v-if="analyzing || loadingRunDetail" :label="analyzing ? t('eclatLoading') : t('loading')" />

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
                <option value="range">{{ t('dateRange') }}</option>
            </select>

            <DatePickerControl
                v-if="filterType === 'range'"
                v-model="filterDateFrom"
                class="mt-3"
                :aria-label="t('startDate')"
                :title="t('startDate')"
            />
            <DatePickerControl
                v-if="filterType === 'range'"
                v-model="filterDateTo"
                class="mt-3"
                :aria-label="t('endDate')"
                :title="t('endDate')"
            />

            <button class="btn btn-outline-success w-100 mt-3" type="button" @click="applyFilter">
                {{ t('applyFilter') }}
            </button>

            <div class="threshold-label mt-4">
                <label class="form-label m-0" for="minSupport">{{ t('support') }}</label>
                <div class="threshold-value">
                    <input
                        v-model.number="minSupport"
                        class="form-control threshold-input"
                        type="number"
                        min="0.1"
                        max="100"
                        step="0.1"
                        :aria-label="t('support')"
                        @blur="minSupport = clampThreshold(minSupport, 0.1)"
                    >
                    <span>%</span>
                </div>
            </div>
            <input id="minSupport" v-model.number="minSupport" class="form-range" type="range" min="0.1" max="100" step="0.1">

            <div class="threshold-label mt-4">
                <label class="form-label m-0" for="minConfidence">{{ t('confidence') }}</label>
                <div class="threshold-value">
                    <input
                        v-model.number="minConfidence"
                        class="form-control threshold-input"
                        type="number"
                        min="0"
                        max="100"
                        step="1"
                        :aria-label="t('confidence')"
                        @blur="minConfidence = clampThreshold(minConfidence, 0)"
                    >
                    <span>%</span>
                </div>
            </div>
            <input id="minConfidence" v-model.number="minConfidence" class="form-range" type="range" min="0" max="100">

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
            <div class="table-toolbar">
                <input v-model="runSearch" class="form-control" :placeholder="t('searchRun')" @keyup.enter="applyRunSearch">
                <button class="btn btn-outline-success" type="button" @click="applyRunSearch">{{ t('search') }}</button>
            </div>
            <SkeletonBlock v-if="loading" :lines="5" />
            <div v-else class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Waktu</th>
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
                            <td>{{ formatDisplayDateTime(run.created_at) }}</td>
                            <td>{{ formatDisplayDateRange(run.date_from, run.date_to) }}</td>
                            <td>{{ run.min_support }}%</td>
                            <td>{{ run.min_confidence }}%</td>
                            <td>{{ run.rule_count }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <PaginationBar :meta="runMeta" always @change="loadRuns" />
        </article>

        <article v-if="latestItemsets.length" class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">{{ t('frequentItemset') }}</p>
                <h2>{{ t('latestRunItemset') }}</h2>
            </div>
            <div class="tid-list">
                <div v-for="itemset in latestItemsets" :key="itemset.key" class="tid-row">
                    <strong>{{ itemset.label }} - {{ itemset.support }}%</strong>
                    <span>Transaction ID: {{ itemset.tid_list.join(', ') }}</span>
                </div>
            </div>
        </article>
    </section>

    <section class="content-panel mt-3">
        <div class="panel-header">
            <p class="eyebrow">{{ t('rules') }}</p>
            <h2>Hasil Association Rule</h2>
        </div>
        <div class="table-toolbar">
            <input v-model="resultSearch" class="form-control" :placeholder="t('searchRule')" @keyup.enter="loadResults(1)">
            <button class="btn btn-outline-success" type="button" @click="loadResults(1)">{{ t('search') }}</button>
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
    </section>

</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { ApiClientError, type PageMeta } from '../../../shared/api/types';
import { formatDisplayDateRange, formatDisplayDateTime } from '../../../shared/dateFormatter';
import DatePickerControl from '../../../shared/components/DatePickerControl.vue';
import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
import FullPageLoader from '../../../shared/components/FullPageLoader.vue';
import PageHeader from '../../../shared/components/PageHeader.vue';
import PaginationBar from '../../../shared/components/PaginationBar.vue';
import SkeletonBlock from '../../../shared/components/SkeletonBlock.vue';
import { t } from '../../../shared/i18n';
import { fetchEclatResults, fetchEclatRun, fetchEclatRuns, runEclatAnalysis } from '../api';
import type { EclatFilterParams, EclatFilterType, EclatItemset, EclatResult, EclatRun, EclatRunDetail, EclatStep } from '../types';

const today = new Date().toISOString().slice(0, 10);
const loading = ref(true);
const analyzing = ref(false);
const loadingRunDetail = ref(false);
const minSupport = ref(0.1);
const minConfidence = ref(30);
const filterType = ref<EclatFilterType>('all');
const filterDateFrom = ref(today);
const filterDateTo = ref(today);
const runs = ref<EclatRun[]>([]);
const results = ref<EclatResult[]>([]);
const latestRunDetail = ref<EclatRunDetail | null>(null);
const activeRunId = ref<number | null>(null);
const runSearch = ref('');
const resultSearch = ref('');
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
    if (filterType.value === 'range') {
        return {
            filter_type: 'range',
            date_from: filterDateFrom.value,
            date_to: filterDateTo.value,
        };
    }

    return { filter_type: 'all' };
});

async function loadRuns(page = runMeta.value?.current_page ?? 1): Promise<void> {
    const response = await fetchEclatRuns({
        page,
        per_page: 5,
        search: runSearch.value.trim() || undefined,
        ...periodParams.value,
    });
    runs.value = response.data;
    runMeta.value = response.meta;
}

async function loadResults(page = resultMeta.value?.current_page ?? 1): Promise<void> {
    const runId = activeRunId.value ?? undefined;
    const response = await fetchEclatResults({
        page,
        per_page: 10,
        search: resultSearch.value.trim() || undefined,
        ...(runId ? {} : periodParams.value),
        run_id: runId,
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
            setLatestRunDetail(response.data);
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
    minSupport.value = clampThreshold(minSupport.value, 0.1);
    minConfidence.value = clampThreshold(minConfidence.value, 0);

    try {
        const response = await runEclatAnalysis(Number(minSupport.value), Number(minConfidence.value), periodParams.value);
        setLatestRunDetail(response.data);
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

async function applyRunSearch(): Promise<void> {
    await loadRuns(1);
    activeRunId.value = runs.value[0]?.id ?? null;

    if (runs.value[0]) {
        const response = await fetchEclatRun(runs.value[0].id);
        setLatestRunDetail(response.data);
    } else {
        latestRunDetail.value = null;
    }

    await loadResults(1);
}

async function selectRun(runId: number): Promise<void> {
    activeRunId.value = runId;
    loadingRunDetail.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await fetchEclatRun(runId);
        setLatestRunDetail(response.data);
        await loadResults(1);
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat ECLAT';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loadingRunDetail.value = false;
    }
}

async function runRecommendedAnalysis(): Promise<void> {
    minSupport.value = 0.1;
    minConfidence.value = 30;
    await submitAnalysis();
}

function setLatestRunDetail(detail: EclatRunDetail): void {
    latestRunDetail.value = detail;
    minSupport.value = Number(detail.run.min_support);
    minConfidence.value = Number(detail.run.min_confidence);
}

function clampThreshold(value: number | string, min: number): number {
    const numeric = Number(value);

    if (!Number.isFinite(numeric)) {
        return min;
    }

    return Number(Math.min(100, Math.max(min, numeric)).toFixed(4));
}

onMounted(loadAll);
</script>
