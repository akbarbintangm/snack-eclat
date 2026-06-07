import { computed, ref } from 'vue';

export type Locale = 'id' | 'en';

const messages = {
    id: {
        appName: 'Snack Eclat',
        subtitle: 'Analisis pola penjualan snack',
        dashboard: 'Dashboard',
        snacks: 'Snack',
        transactions: 'Transaksi',
        eclat: 'ECLAT',
        reports: 'Laporan',
        docs: 'Dokumentasi API',
        theme: 'Tema',
        language: 'Bahasa',
        loading: 'Memuat data...',
        retry: 'Coba lagi',
        search: 'Cari',
        save: 'Simpan',
        analyze: 'Jalankan Analisis',
        page: 'Halaman',
        previous: 'Sebelumnya',
        next: 'Berikutnya',
        noData: 'Belum ada data.',
    },
    en: {
        appName: 'Snack Eclat',
        subtitle: 'Snack sales pattern analysis',
        dashboard: 'Dashboard',
        snacks: 'Snacks',
        transactions: 'Transactions',
        eclat: 'ECLAT',
        reports: 'Reports',
        docs: 'API Docs',
        theme: 'Theme',
        language: 'Language',
        loading: 'Loading data...',
        retry: 'Retry',
        search: 'Search',
        save: 'Save',
        analyze: 'Run Analysis',
        page: 'Page',
        previous: 'Previous',
        next: 'Next',
        noData: 'No data yet.',
    },
};

const storedLocale = localStorage.getItem('snack-eclat.locale') as Locale | null;
export const locale = ref<Locale>(storedLocale === 'en' ? 'en' : 'id');
export const activeMessages = computed(() => messages[locale.value]);

export function setLocale(nextLocale: Locale): void {
    locale.value = nextLocale;
    localStorage.setItem('snack-eclat.locale', nextLocale);
}

export function t(key: keyof typeof messages.id): string {
    return messages[locale.value][key] ?? messages.id[key] ?? key;
}
