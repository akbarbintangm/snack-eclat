import { ref } from 'vue';

export type ThemeMode = 'light' | 'dark';

const storedTheme = localStorage.getItem('snack-eclat.theme') as ThemeMode | null;
export const themeMode = ref<ThemeMode>(storedTheme === 'dark' ? 'dark' : 'light');

export function applyTheme(mode: ThemeMode = themeMode.value): void {
    document.documentElement.dataset.theme = mode;
    localStorage.setItem('snack-eclat.theme', mode);
}

export function toggleTheme(): void {
    themeMode.value = themeMode.value === 'dark' ? 'light' : 'dark';
    applyTheme(themeMode.value);
}
