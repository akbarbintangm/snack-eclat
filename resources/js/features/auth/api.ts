import { getData, postData } from '../../shared/api/http';
import type { ApiResponse } from '../../shared/api/types';
import type { AuthSession, LoginPayload, SignupPayload } from './types';

export function login(payload: LoginPayload): Promise<ApiResponse<AuthSession>> {
    return postData<AuthSession>('/auth/login', payload as unknown as Record<string, unknown>);
}

export function signup(payload: SignupPayload): Promise<ApiResponse<AuthSession>> {
    return postData<AuthSession>('/auth/signup', payload as unknown as Record<string, unknown>);
}

export function fetchCurrentUser(): Promise<ApiResponse<AuthSession['user']>> {
    return getData<AuthSession['user']>('/auth/me');
}

export function logout(): Promise<ApiResponse<null>> {
    return postData<null>('/auth/logout', {});
}
