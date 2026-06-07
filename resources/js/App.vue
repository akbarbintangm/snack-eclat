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

                    <div id="mainNavigation" class="collapse navbar-collapse">
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
                        </div>
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
import FullPageLoader from './shared/components/FullPageLoader.vue';
import { locale, setLocale, t, type Locale } from './shared/i18n';
import { isRouteLoading } from './shared/routerLoading';
import { applyTheme, themeMode, toggleTheme } from './shared/theme/useTheme';

const navItems = computed(() => [
    { to: '/', label: t('dashboard') },
    { to: '/snacks', label: t('snacks') },
    { to: '/transactions', label: t('transactions') },
    { to: '/eclat', label: t('eclat') },
    { to: '/reports', label: t('reports') },
    { to: '/docs', label: t('docs') },
]);

function switchLocale(): void {
    setLocale((locale.value === 'id' ? 'en' : 'id') as Locale);
}

onMounted(() => applyTheme());
</script>
