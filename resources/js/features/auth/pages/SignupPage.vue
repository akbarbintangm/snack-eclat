<template>
    <PageHeader eyebrow="Sign Up" :title="t('signup')" description="Daftarkan akun baru untuk mengelola data transaksi dan proses ECLAT." />

    <ErrorBanner :message="errorMessage" :status-code="errorStatus" />

    <form class="content-panel mx-auto" style="max-width: 520px;" @submit.prevent="submitSignup">
        <div class="panel-header">
            <p class="eyebrow">Akun</p>
            <h2>Registrasi User</h2>
        </div>

        <label class="form-label" for="name">Nama</label>
        <input id="name" v-model="form.name" class="form-control" autocomplete="name">

        <label class="form-label mt-3" for="username">Username</label>
        <input id="username" v-model="form.username" class="form-control" autocomplete="username" required>

        <label class="form-label mt-3" for="email">Email</label>
        <input id="email" v-model="form.email" class="form-control" autocomplete="email" type="email">

        <label class="form-label mt-3" for="level">Level</label>
        <select id="level" v-model="form.level" class="form-select">
            <option value="admin">Admin</option>
            <option value="owner">Owner</option>
        </select>

        <label class="form-label mt-3" for="password">Password</label>
        <input id="password" v-model="form.password" class="form-control" autocomplete="new-password" required type="password">

        <label class="form-label mt-3" for="passwordConfirmation">Konfirmasi Password</label>
        <input id="passwordConfirmation" v-model="form.password_confirmation" class="form-control" autocomplete="new-password" required type="password">

        <button class="btn btn-success w-100 mt-4" type="submit" :disabled="loading">
            {{ loading ? t('loading') : t('signup') }}
        </button>

        <RouterLink class="btn btn-outline-success w-100 mt-2" to="/login">{{ t('login') }}</RouterLink>
    </form>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ApiClientError } from '../../../shared/api/types';
import { setAuthSession } from '../../../shared/auth';
import ErrorBanner from '../../../shared/components/ErrorBanner.vue';
import PageHeader from '../../../shared/components/PageHeader.vue';
import { t } from '../../../shared/i18n';
import { signup } from '../api';
import type { SignupPayload } from '../types';

const router = useRouter();
const loading = ref(false);
const form = ref<SignupPayload>({
    username: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    level: 'admin',
});
const errorMessage = ref('');
const errorStatus = ref<number | undefined>();

async function submitSignup(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    errorStatus.value = undefined;

    try {
        const response = await signup({
            username: form.value.username,
            name: form.value.name || undefined,
            email: form.value.email || undefined,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
            level: form.value.level,
        });
        setAuthSession(response.data.token, response.data.user);
        await router.push('/');
    } catch (error) {
        errorMessage.value = error instanceof ApiClientError ? error.message : 'Gagal sign up';
        errorStatus.value = error instanceof ApiClientError ? error.statusCode : undefined;
    } finally {
        loading.value = false;
    }
}
</script>
