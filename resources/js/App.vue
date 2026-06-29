<template>
    <div class="app-shell">
        <FullPageLoader v-if="isRouteLoading" :label="t('loading')" />

        <header class="topbar">
            <div class="container-xl">
                <nav class="navbar navbar-expand-lg gap-3 px-0">
                    <RouterLink class="navbar-brand d-flex align-items-center gap-3" to="/">
                        <span class="brand-mark">SE</span>
                        <span>
                            <span class="brand-title">{{ t('appName') }}</span>
                            <span class="brand-subtitle">{{ t('subtitle') }}</span>
                        </span>
                    </RouterLink>

                    <button
                        v-if="isAuthenticated"
                        class="navbar-toggler"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#mainNavigation"
                        aria-controls="mainNavigation"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                    >
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div v-if="isAuthenticated" id="mainNavigation" class="collapse navbar-collapse">
                        <div class="navbar-nav nav-pills ms-lg-auto">
                            <RouterLink v-for="item in navItems" :key="item.to" class="nav-link" :to="item.to">
                                {{ item.label }}
                            </RouterLink>
                        </div>
                        <div class="toolbar-actions ms-lg-3 mt-3 mt-lg-0">
                            <button class="icon-button" type="button" :title="t('theme')" @click="toggleTheme">
                                {{ themeMode === 'dark' ? 'D' : 'L' }}
                            </button>
                            <button class="text-button" type="button" :title="t('language')" @click="switchLocale">
                                {{ locale.toUpperCase() }}
                            </button>
                            <div class="dropdown">
                                <button
                                    class="text-button dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    :title="t('profile')"
                                >
                                    {{ currentUser?.username }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-end profile-menu">
                                    <div class="profile-menu__identity">
                                        <strong>{{ currentUser?.name }}</strong>
                                        <span>{{ currentUser?.email }}</span>
                                        <small>{{ currentUser?.level }}</small>
                                    </div>
                                    <button class="dropdown-item" type="button" @click="submitLogout">
                                        {{ t('logout') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="toolbar-actions ms-auto">
                        <button class="icon-button" type="button" :title="t('theme')" @click="toggleTheme">
                            {{ themeMode === 'dark' ? 'D' : 'L' }}
                        </button>
                        <button class="text-button" type="button" :title="t('language')" @click="switchLocale">
                            {{ locale.toUpperCase() }}
                        </button>
                        <RouterLink class="btn btn-outline-success" to="/login">{{ t('login') }}</RouterLink>
                    </div>
                </nav>
            </div>
        </header>

        <main class="page-wrap">
            <RouterView />
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { logout } from './features/auth/api';
import { clearAuthSession, currentUser, isAuthenticated } from './shared/auth';
import FullPageLoader from './shared/components/FullPageLoader.vue';
import { locale, setLocale, t, type Locale } from './shared/i18n';
import { isRouteLoading } from './shared/routerLoading';
import { applyTheme, themeMode, toggleTheme } from './shared/theme/useTheme';

const navItems = computed(() => {
    const role = currentUser.value?.level;
    const items = [
        { to: '/', label: t('dashboard'), roles: ['admin', 'owner'] },
        { to: '/snacks', label: t('snacks'), roles: ['admin'] },
        { to: '/transactions', label: t('transactions'), roles: ['admin'] },
        { to: '/eclat', label: t('eclat'), roles: ['admin'] },
        { to: '/reports', label: t('reports'), roles: ['owner'] },
        { to: '/docs', label: t('docs'), roles: ['admin', 'owner'] },
    ];

    return items.filter((item) => !role || item.roles.includes(role));
});
const router = useRouter();

function switchLocale(): void {
    setLocale((locale.value === 'id' ? 'en' : 'id') as Locale);
}

async function submitLogout(): Promise<void> {
    try {
        await logout();
    } finally {
        clearAuthSession();
        await router.push('/login');
    }
}

onMounted(() => applyTheme());
</script>
