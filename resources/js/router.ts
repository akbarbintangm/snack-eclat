import { createRouter, createWebHistory } from 'vue-router';
import { isRouteLoading } from './shared/routerLoading';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'dashboard', component: () => import('./features/dashboard/pages/DashboardPage.vue'), meta: { title: 'Dashboard' } },
        { path: '/snacks', name: 'snacks', component: () => import('./features/snacks/pages/SnacksPage.vue'), meta: { title: 'Snack' } },
        { path: '/transactions', name: 'transactions', component: () => import('./features/transactions/pages/TransactionsPage.vue'), meta: { title: 'Transaksi' } },
        { path: '/eclat', name: 'eclat', component: () => import('./features/eclat/pages/EclatPage.vue'), meta: { title: 'ECLAT' } },
        { path: '/reports', name: 'reports', component: () => import('./features/reports/pages/ReportsPage.vue'), meta: { title: 'Laporan' } },
        { path: '/docs', name: 'docs', component: () => import('./features/documentation/pages/SwaggerPage.vue'), meta: { title: 'Dokumentasi API' } },
        { path: '/transaksi', redirect: '/transactions' },
        { path: '/analisis', redirect: '/eclat' },
        { path: '/dokumentasi', redirect: '/docs' },
    ],
});

router.beforeEach(() => {
    isRouteLoading.value = true;
});

router.afterEach((to) => {
    document.title = `${String(to.meta.title ?? 'Aplikasi')} - Snack Eclat`;
    window.setTimeout(() => {
        isRouteLoading.value = false;
    }, 160);
});

router.onError((error) => {
    console.error('[Snack Eclat Router]', error);
    isRouteLoading.value = false;
});

export default router;
