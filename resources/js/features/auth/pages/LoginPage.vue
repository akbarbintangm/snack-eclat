<template>
    <PageHeader eyebrow="Login" :title="t('login')" description="Gunakan akun Admin atau Owner untuk mengakses transaksi, proses ECLAT, dan laporan." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" />

    <form class="content-panel mx-auto" style="max-width: 440px;" @submit.prevent="submitLogin">
        <div class="panel-header">
            <p class="eyebrow">Akun</p>
            <h2>Login Snack Eclat</h2>
        </div>

        <label class="form-label" for="username">Username</label>
        <input id="username" v-model="form.username" class="form-control" autocomplete="username" required>

        <label class="form-label mt-3" for="password">Password</label>
        <input id="password" v-model="form.password" class="form-control" autocomplete="current-password" required type="password">

        <button class="btn btn-success w-100 mt-4" type="submit" :disabled="loading">
            {{ loading ? t('loading') : t('login') }}
        </button>

        <RouterLink class="btn btn-outline-success w-100 mt-2" to="/signup">{{ t('signup') }}</RouterLink>
    </form>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ApiClientError } from '../../../shared/api/types';
import { setAuthSession } from '../../../shared/auth';
import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
import PageHeader from '../../../shared/components/PageHeader.vue';
import { t } from '../../../shared/i18n';
import { login } from '../api';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const form = ref({ username: '', password: '' });
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

async function submitLogin(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await login(form.value);
        setAuthSession(response.data.token, response.data.user);
        await router.push(String(route.query.redirect ?? '/'));
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal login';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loading.value = false;
    }
}
</script>
