import type { AuthUser } from '../../shared/auth';

export interface AuthSession {
    token: string;
    user: AuthUser;
}

export interface LoginPayload {
    username: string;
    password: string;
}

export interface SignupPayload {
    username: string;
    name?: string;
    email?: string;
    password: string;
    password_confirmation: string;
    level?: 'admin' | 'owner';
}
