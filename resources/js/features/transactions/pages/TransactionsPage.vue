<template>
    <PageHeader eyebrow="Transaksi" :title="t('transactions')" description="Input transaksi snack dan lihat detail item yang menjadi dasar analisis ECLAT." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" :retry="loadAll" />

    <section class="feature-grid">
        <form class="content-panel compact-form" @submit.prevent="submitTransaction">
            <div class="panel-header">
                <p class="eyebrow">Entry</p>
                <h2>Tambah Transaksi</h2>
            </div>
            <label class="form-label" for="referenceNo">Referensi</label>
            <input id="referenceNo" v-model="form.reference_no" class="form-control" placeholder="Contoh: T26">

            <label class="form-label mt-3" for="transactionDate">Tanggal</label>
            <input id="transactionDate" v-model="form.transaction_date" class="form-control" type="date" required>

            <div class="mt-3">
                <label class="form-label">Item Snack</label>
                <SkeletonBlock v-if="snackLoading" :lines="3" />
                <label v-for="snack in snackOptions" v-else :key="snack.id" class="check-row">
                    <input v-model="selectedSnackIds" class="form-check-input" type="checkbox" :value="snack.id">
                    <span>{{ snack.name }}</span>
                </label>
            </div>

            <button class="btn btn-success w-100 mt-3" type="submit" :disabled="saving || selectedSnackIds.length === 0">
                {{ saving ? t('loading') : t('save') }}
            </button>
        </form>

        <article class="content-panel">
            <SkeletonBlock v-if="loading" :lines="7" />
            <div v-else class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Tanggal</th>
                            <th>Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="transaction in transactions" :key="transaction.id">
                            <td class="fw-semibold">{{ transaction.reference_no ?? `#${transaction.id}` }}</td>
                            <td>{{ transaction.transaction_date }}</td>
                            <td>
                                <span v-for="detail in transaction.details" :key="detail.id" class="item-badge">
                                    {{ detail.snack?.name }} x{{ detail.quantity }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="transactions.length === 0" class="empty-state">{{ t('noData') }}</p>
            </div>
            <PaginationBar :meta="meta" @change="loadTransactions" />
        </article>
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
import { fetchSnacks } from '../../snacks/api';
import type { Snack } from '../../snacks/types';
import { createTransaction, fetchTransactions } from '../api';
import type { SalesTransaction } from '../types';

const today = new Date().toISOString().slice(0, 10);
const loading = ref(true);
const snackLoading = ref(true);
const saving = ref(false);
const transactions = ref<SalesTransaction[]>([]);
const snackOptions = ref<Snack[]>([]);
const selectedSnackIds = ref<number[]>([]);
const meta = ref<PageMeta | null>(null);
const form = ref({ reference_no: '', transaction_date: today });
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

async function loadSnacks(): Promise<void> {
    snackLoading.value = true;
    const response = await fetchSnacks({ per_page: 100 });
    snackOptions.value = response.data;
    snackLoading.value = false;
}

async function loadTransactions(page = meta.value?.current_page ?? 1): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await fetchTransactions({ page, per_page: 10 });
        transactions.value = response.data;
        meta.value = response.meta;
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat transaksi';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loading.value = false;
    }
}

async function loadAll(): Promise<void> {
    try {
        await Promise.all([loadSnacks(), loadTransactions(1)]);
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat data';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
        snackLoading.value = false;
        loading.value = false;
    }
}

async function submitTransaction(): Promise<void> {
    saving.value = true;

    try {
        await createTransaction({
            reference_no: form.value.reference_no || null,
            transaction_date: form.value.transaction_date,
            items: selectedSnackIds.value.map((snackId) => ({ snack_id: snackId, quantity: 1 })),
        });
        form.value.reference_no = '';
        selectedSnackIds.value = [];
        await loadTransactions(1);
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal menyimpan transaksi';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        saving.value = false;
    }
}

onMounted(loadAll);
</script>
