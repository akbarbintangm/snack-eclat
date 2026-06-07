import { createRouter, createWebHistory } from 'vue-router';
import AnalysisView from './views/AnalysisView.vue';
import DashboardView from './views/DashboardView.vue';
import DocumentationView from './views/DocumentationView.vue';
import TransactionsView from './views/TransactionsView.vue';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: DashboardView,
        meta: { title: 'Dashboard' },
    },
    {
        path: '/transaksi',
        name: 'transactions',
        component: TransactionsView,
        meta: { title: 'Data Transaksi' },
    },
    {
        path: '/analisis',
        name: 'analysis',
        component: AnalysisView,
        meta: { title: 'Analisis ECLAT' },
    },
    {
        path: '/dokumentasi',
        name: 'documentation',
        component: DocumentationView,
        meta: { title: 'Dokumentasi' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.afterEach((to) => {
    document.title = `${to.meta.title ?? 'Aplikasi'} - Snack Eclat`;
});

export default router;
