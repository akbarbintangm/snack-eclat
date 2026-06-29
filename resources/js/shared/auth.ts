import { computed, ref } from 'vue';

const TOKEN_KEY = 'snack-eclat.jwt';
const USER_KEY = 'snack-eclat.user';

export interface AuthUser {
    id: number;
    username: string;
    name: string;
    email: string;
    level: string;
}

function readStoredUser(): AuthUser | null {
    const raw = localStorage.getItem(USER_KEY);

    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw) as AuthUser;
    } catch {
        localStorage.removeItem(USER_KEY);

        return null;
    }
}

export const authToken = ref(localStorage.getItem(TOKEN_KEY));
export const currentUser = ref<AuthUser | null>(readStoredUser());
export const isAuthenticated = computed(() => Boolean(authToken.value));

export function hasAuthToken(): boolean {
    return Boolean(localStorage.getItem(TOKEN_KEY));
}

export function setAuthSession(token: string, user: AuthUser): void {
    authToken.value = token;
    currentUser.value = user;
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

export function clearAuthSession(): void {
    authToken.value = null;
    currentUser.value = null;
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
}
