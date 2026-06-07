<script setup>
import { computed, ref } from 'vue';
import { buildEclatSummary, formatPercent, reportThresholds } from '../data/snackEclat';

const minSupport = ref(reportThresholds.minSupport);
const minConfidence = ref(reportThresholds.minConfidence);

const summary = computed(() => buildEclatSummary(Number(minSupport.value), Number(minConfidence.value)));
</script>

<template>
    <section class="page-heading">
        <div>
            <p class="eyebrow">Simulasi ECLAT</p>
            <h1>Frequent Itemset dan Association Rule</h1>
            <p class="lead-text">
                Analisis menghitung TID List, support, dan confidence dari dataset transaksi snack
                Toko ABC.
            </p>
        </div>
    </section>

    <section class="analysis-grid">
        <div class="tool-panel">
            <div class="panel-header">
                <p class="eyebrow">Parameter</p>
                <h2>Ambang Analisis</h2>
            </div>

            <label class="form-label" for="minSupport">
                Minimum support
                <strong>{{ minSupport }}%</strong>
            </label>
            <input
                id="minSupport"
                v-model="minSupport"
                class="form-range"
                type="range"
                min="0"
                max="100"
                step="1"
            >

            <label class="form-label mt-4" for="minConfidence">
                Minimum confidence
                <strong>{{ minConfidence }}%</strong>
            </label>
            <input
                id="minConfidence"
                v-model="minConfidence"
                class="form-range"
                type="range"
                min="0"
                max="100"
                step="1"
            >

            <div class="threshold-summary">
                <span>{{ summary.frequentItemsets.length }} frequent itemset</span>
                <span>{{ summary.rules.length }} rule lolos</span>
            </div>
        </div>

        <div class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Rule lolos</p>
                <h2>Rekomendasi Penjualan</h2>
            </div>
            <div v-if="summary.rules.length" class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th scope="col">Rule</th>
                            <th scope="col" class="text-end">Support</th>
                            <th scope="col" class="text-end">Confidence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="rule in summary.rules" :key="rule.key">
                            <td>{{ rule.label }}</td>
                            <td class="text-end">{{ formatPercent(rule.support) }}</td>
                            <td class="text-end fw-semibold">{{ formatPercent(rule.confidence) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="empty-state">Belum ada rule yang memenuhi ambang saat ini.</p>
        </div>
    </section>

    <section class="table-grid">
        <div class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">Frequent itemset</p>
                <h2>Itemset Lolos Support</h2>
            </div>
            <div class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th scope="col">Itemset</th>
                            <th scope="col" class="text-end">Jumlah</th>
                            <th scope="col" class="text-end">Support</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="itemset in summary.frequentItemsets" :key="itemset.key">
                            <td>{{ itemset.label }}</td>
                            <td class="text-end">{{ itemset.count }}</td>
                            <td class="text-end">{{ formatPercent(itemset.support) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-panel">
            <div class="panel-header">
                <p class="eyebrow">TID List</p>
                <h2>Transformasi Vertikal</h2>
            </div>
            <div class="tid-list">
                <div v-for="itemset in summary.tidList" :key="itemset.key" class="tid-row">
                    <strong>{{ itemset.label }}</strong>
                    <span>{{ itemset.tids.join(', ') }}</span>
                </div>
            </div>
        </div>
    </section>
</template>
