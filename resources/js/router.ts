import { createRouter, createWebHistory } from 'vue-router';
import { currentUser, hasAuthToken } from './shared/auth';
import { isRouteLoading } from './shared/routerLoading';

const ownerRoles = ['owner'];
const sharedRoles = ['admin', 'owner'];
const operationalRoles = ['admin'];

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/login', name: 'login', component: () => import('./features/auth/pages/LoginPage.vue'), meta: { title: 'Login', public: true } },
        { path: '/signup', name: 'signup', component: () => import('./features/auth/pages/SignupPage.vue'), meta: { title: 'Sign Up', public: true } },
        { path: '/', name: 'dashboard', component: () => import('./features/dashboard/pages/DashboardPage.vue'), meta: { title: 'Dashboard', roles: sharedRoles } },
        { path: '/snacks', name: 'snacks', component: () => import('./features/snacks/pages/SnacksPage.vue'), meta: { title: 'Snack', roles: operationalRoles } },
        { path: '/transactions', name: 'transactions', component: () => import('./features/transactions/pages/TransactionsPage.vue'), meta: { title: 'Transaksi', roles: operationalRoles } },
        { path: '/eclat', name: 'eclat', component: () => import('./features/eclat/pages/EclatPage.vue'), meta: { title: 'ECLAT', roles: operationalRoles } },
        { path: '/reports', name: 'reports', component: () => import('./features/reports/pages/ReportsPage.vue'), meta: { title: 'Laporan', roles: ownerRoles } },
        { path: '/docs', name: 'docs', component: () => import('./features/documentation/pages/SwaggerPage.vue'), meta: { title: 'Dokumentasi API', roles: sharedRoles } },
        { path: '/transaksi', redirect: '/transactions' },
        { path: '/analisis', redirect: '/eclat' },
        { path: '/dokumentasi', redirect: '/docs' },
    ],
});

router.beforeEach((to) => {
    isRouteLoading.value = true;

    const publicRoute = Boolean(to.meta.public);

    if (!publicRoute && !hasAuthToken()) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (publicRoute && hasAuthToken()) {
        return { name: 'dashboard' };
    }

    const roles = to.meta.roles as string[] | undefined;

    if (roles && currentUser.value?.level && !roles.includes(currentUser.value.level)) {
        return { name: 'dashboard' };
    }
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
