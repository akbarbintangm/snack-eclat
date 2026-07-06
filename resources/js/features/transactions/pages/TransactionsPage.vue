<template>
    <FullPageLoader v-if="importing || deleting" :label="importing ? t('uploadExcelLoading') : t('deleteTransactionDataLoading')" />

    <PageHeader eyebrow="Transaksi" :title="t('transactions')" description="Input transaksi snack dan lihat detail item yang menjadi dasar analisis ECLAT." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" :retry="loadAll" />

    <section class="feature-grid">
        <form class="content-panel compact-form" @submit.prevent="submitTransaction">
            <div class="panel-header">
                <p class="eyebrow">Excel</p>
                <h2>{{ t('uploadDataExcel') }}</h2>
            </div>

            <label class="form-label" for="transactionExcel">{{ t('transactionFile') }}</label>
            <input id="transactionExcel" ref="transactionExcelInput" class="form-control" accept=".xlsx" type="file" @change="selectImportFile">

            <button class="btn btn-outline-success w-100 mt-3" type="button" :disabled="importing || !importFile" @click="submitImport">
                {{ t('uploadExcel') }}
            </button>

            <button class="btn btn-outline-danger w-100 mt-2" type="button" :disabled="deleting || importing" @click="submitDeleteTransactionData">
                {{ deleting ? t('loading') : t('deleteTransactionData') }}
            </button>

            <p v-if="importSummary" class="empty-state mt-3">
                {{ importSummary.rows_imported }} {{ t('uploadSummary') }}, {{ importSummary.transactions_created }} transaksi baru, {{ importSummary.snacks_created }} snack baru.
            </p>

            <hr class="my-4">

            <div class="panel-header">
                <p class="eyebrow">Entry</p>
                <h2>{{ t('addTransaction') }}</h2>
            </div>
            <label class="form-label" for="referenceNo">{{ t('reference') }}</label>
            <input id="referenceNo" v-model="form.reference_no" class="form-control" placeholder="Contoh: T26">

            <label class="form-label mt-3" for="transactionDate">{{ t('date') }}</label>
            <DatePickerControl
                id="transactionDate"
                v-model="form.transaction_date"
                required
            />

            <div class="mt-3">
                <label class="form-label">{{ t('itemSnack') }}</label>
                <SkeletonBlock v-if="snackLoading" :lines="3" />
                <div v-else class="multi-select">
                    <button class="form-control multi-select-trigger" type="button" :aria-expanded="snackDropdownOpen" @click="toggleSnackDropdown">
                        <span>{{ selectedSnackIds.length ? `${selectedSnackIds.length} ${t('selectedSnack')}` : t('chooseSnack') }}</span>
                        <span aria-hidden="true">v</span>
                    </button>
                    <div v-if="snackDropdownOpen" class="multi-select-panel">
                        <input v-model="snackSearch" class="form-control" :placeholder="t('searchSnack')">
                        <div class="multi-select-options">
                            <label v-for="snack in filteredSnackOptions" :key="snack.id" class="check-row">
                                <input v-model="pendingSnackIds" class="form-check-input" type="checkbox" :value="snack.id">
                                <span>{{ snack.name }}</span>
                            </label>
                            <p v-if="filteredSnackOptions.length === 0" class="empty-state">{{ t('noData') }}</p>
                        </div>
                        <button class="btn btn-success w-100 mt-3" type="button" @click="applySnackSelection">
                            {{ t('applySelection') }}
                        </button>
                    </div>
                    <div v-if="selectedSnacks.length" class="selected-preview">
                        <span v-for="snack in selectedSnacks" :key="snack.id" class="item-badge">{{ snack.name }}</span>
                    </div>
                </div>
            </div>

            <button class="btn btn-success w-100 mt-3" type="submit" :disabled="saving || selectedSnackIds.length === 0">
                {{ saving ? t('loading') : t('save') }}
            </button>
        </form>

        <article class="content-panel">
            <div class="table-toolbar">
                <input v-model="transactionSearch" class="form-control" :placeholder="t('searchTransaction')" @keyup.enter="applyTransactionFilters">
                <DatePickerControl
                    v-model="transactionDateFrom"
                    :aria-label="t('startDate')"
                    :title="t('startDate')"
                />
                <DatePickerControl
                    v-model="transactionDateTo"
                    :aria-label="t('endDate')"
                    :title="t('endDate')"
                />
                <button class="btn btn-outline-success" type="button" @click="applyTransactionFilters">{{ t('search') }}</button>
            </div>
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
                            <td>{{ formatDisplayDate(transaction.transaction_date) }}</td>
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
    import { computed, onMounted, ref } from 'vue';
    import { ApiClientError, type PageMeta } from '../../../shared/api/types';
    import { formatDisplayDate } from '../../../shared/dateFormatter';
    import DatePickerControl from '../../../shared/components/DatePickerControl.vue';
    import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
    import FullPageLoader from '../../../shared/components/FullPageLoader.vue';
    import PageHeader from '../../../shared/components/PageHeader.vue';
    import PaginationBar from '../../../shared/components/PaginationBar.vue';
    import SkeletonBlock from '../../../shared/components/SkeletonBlock.vue';
    import { t } from '../../../shared/i18n';
    import { fetchSnacks } from '../../snacks/api';
    import type { Snack } from '../../snacks/types';
    import { createTransaction, deleteTransactionData, fetchTransactions, importTransactions } from '../api';
    import type { SalesTransaction, TransactionImportSummary } from '../types';

    const today = new Date().toISOString().slice(0, 10);
    const loading = ref(true);
    const snackLoading = ref(true);
    const saving = ref(false);
    const importing = ref(false);
    const deleting = ref(false);
    const transactions = ref<SalesTransaction[]>([]);
    const snackOptions = ref<Snack[]>([]);
    const selectedSnackIds = ref<number[]>([]);
    const pendingSnackIds = ref<number[]>([]);
    const snackSearch = ref('');
    const snackDropdownOpen = ref(false);
    const transactionSearch = ref('');
    const transactionDateFrom = ref('');
    const transactionDateTo = ref('');
    const importFile = ref<File | null>(null);
    const importSummary = ref<TransactionImportSummary | null>(null);
    const transactionExcelInput = ref<HTMLInputElement | null>(null);
    const meta = ref<PageMeta | null>(null);
    const form = ref({ reference_no: '', transaction_date: today });
    const errorMessage = ref('');
    const errorStatus = ref<number | undefined>();

    const filteredSnackOptions = computed(() => {
        const search = snackSearch.value.trim().toLowerCase();

        if (!search) {
            return snackOptions.value;
        }

        return snackOptions.value.filter((snack) => snack.name.toLowerCase().includes(search));
    });

    const selectedSnacks = computed(() => snackOptions.value.filter((snack) => selectedSnackIds.value.includes(snack.id)));

    async function loadSnacks(): Promise<void> {
        snackLoading.value = true;
        const response = await fetchSnacks({ per_page: 500 });
        snackOptions.value = response.data;
        snackLoading.value = false;
    }

    async function loadTransactions(page = meta.value?.current_page ?? 1): Promise<void> {
        loading.value = true;
        errorMessage.value = '';
        errorStatus.value = undefined;

        try {
            const response = await fetchTransactions({
                page,
                per_page: 10,
                search: transactionSearch.value.trim() || undefined,
                date_from: transactionDateFrom.value || undefined,
                date_to: transactionDateTo.value || undefined,
            });
            transactions.value = response.data;
            meta.value = response.meta;
        } catch (error) {
            errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat transaksi';
            errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
        } finally {
            loading.value = false;
        }
    }

    async function applyTransactionFilters(): Promise<void> {
        await loadTransactions(1);
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
            pendingSnackIds.value = [];
            snackSearch.value = '';
            await loadTransactions(1);
        } catch (error) {
            errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal menyimpan transaksi';
            errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
        } finally {
            saving.value = false;
        }
    }

    function toggleSnackDropdown(): void {
        pendingSnackIds.value = [...selectedSnackIds.value];
        snackDropdownOpen.value = !snackDropdownOpen.value;
    }

    function applySnackSelection(): void {
        selectedSnackIds.value = [...pendingSnackIds.value];
        snackDropdownOpen.value = false;
    }

    function selectImportFile(event: Event): void {
        const input = event.target as HTMLInputElement;
        importFile.value = input.files?.[0] ?? null;
        importSummary.value = null;
    }

    async function submitImport(): Promise<void> {
        if (!importFile.value) {
            return;
        }

        importing.value = true;
        errorMessage.value = '';
        errorStatus.value = undefined;

        try {
            const response = await importTransactions(importFile.value);
            importSummary.value = response.data;
            importFile.value = null;
            if (transactionExcelInput.value) {
                transactionExcelInput.value.value = '';
            }
            await Promise.all([loadSnacks(), loadTransactions(1)]);
        } catch (error) {
            errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal upload Excel';
            errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
        } finally {
            importing.value = false;
        }
    }

    async function submitDeleteTransactionData(): Promise<void> {
        if (!window.confirm(t('deleteTransactionDataConfirm'))) {
            return;
        }

        deleting.value = true;
        errorMessage.value = '';
        errorStatus.value = undefined;

        try {
            await deleteTransactionData();
            importSummary.value = null;
            transactionSearch.value = '';
            transactionDateFrom.value = '';
            transactionDateTo.value = '';
            await Promise.all([loadSnacks(), loadTransactions(1)]);
        } catch (error) {
            errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal menghapus data transaksi';
            errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
        } finally {
            deleting.value = false;
        }
    }

    onMounted(loadAll);
</script>
