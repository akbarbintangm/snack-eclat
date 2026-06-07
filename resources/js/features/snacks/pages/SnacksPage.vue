<template>
    <PageHeader eyebrow="Master Data" :title="t('snacks')" description="Kelola produk snack yang akan muncul pada transaksi dan proses TID List." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" :retry="loadSnacks" />

    <section class="feature-grid">
        <form class="content-panel compact-form" @submit.prevent="submitSnack">
            <div class="panel-header">
                <p class="eyebrow">Entry</p>
                <h2>Tambah Snack</h2>
            </div>
            <label class="form-label" for="snackName">Nama Snack</label>
            <input id="snackName" v-model="form.name" class="form-control" required maxlength="100">
            <button class="btn btn-success w-100 mt-3" type="submit" :disabled="saving">
                {{ saving ? t('loading') : t('save') }}
            </button>
        </form>

        <article class="content-panel">
            <div class="table-toolbar">
                <input v-model="search" class="form-control" :placeholder="t('search')" @keyup.enter="loadSnacks(1)">
                <button class="btn btn-outline-success" type="button" @click="loadSnacks(1)">{{ t('search') }}</button>
            </div>
            <SkeletonBlock v-if="loading" :lines="6" />
            <div v-else class="table-responsive">
                <table class="table align-middle data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="snack in snacks" :key="snack.id">
                            <td>{{ snack.id }}</td>
                            <td class="fw-semibold">{{ snack.name }}</td>
                            <td><span class="status-pill">{{ snack.status }}</span></td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="snacks.length === 0" class="empty-state">{{ t('noData') }}</p>
            </div>
            <PaginationBar :meta="meta" @change="loadSnacks" />
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
import { createSnack, fetchSnacks } from '../api';
import type { Snack } from '../types';

const loading = ref(true);
const saving = ref(false);
const snacks = ref<Snack[]>([]);
const meta = ref<PageMeta | null>(null);
const search = ref('');
const form = ref({ name: '' });
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

async function loadSnacks(page = meta.value?.current_page ?? 1): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await fetchSnacks({ page, per_page: 10, search: search.value || undefined });
        snacks.value = response.data;
        meta.value = response.meta;
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal memuat snack';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loading.value = false;
    }
}

async function submitSnack(): Promise<void> {
    saving.value = true;

    try {
        await createSnack({ name: form.value.name });
        form.value.name = '';
        await loadSnacks(1);
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal menyimpan snack';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        saving.value = false;
    }
}

onMounted(() => loadSnacks(1));
</script>
