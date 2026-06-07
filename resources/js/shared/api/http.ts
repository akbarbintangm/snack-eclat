import axios, { AxiosError } from 'axios';
import type { ApiResponse } from './types';
import { ApiClientError } from './types';

export const http = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    timeout: 15000,
});

http.interceptors.request.use((config) => {
    const token = localStorage.getItem('snack-eclat.jwt');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

http.interceptors.response.use(
    (response) => response,
    (error: AxiosError<ApiResponse<unknown>>) => {
        const statusCode = error.response?.status ?? 0;
        const message = error.response?.data?.message
            ?? error.message
            ?? 'Network error';

        console.error('[Snack Eclat API]', {
            statusCode,
            message,
            url: error.config?.url,
            method: error.config?.method,
            errors: error.response?.data?.errors,
        });

        throw new ApiClientError(message, statusCode, error.response?.data?.errors);
    },
);

export async function getData<T>(url: string, params?: Record<string, unknown>): Promise<ApiResponse<T>> {
    const response = await http.get<ApiResponse<T>>(url, { params });

    return response.data;
}

export async function postData<T>(url: string, payload: Record<string, unknown>): Promise<ApiResponse<T>> {
    const response = await http.post<ApiResponse<T>>(url, payload);

    return response.data;
}

export async function putData<T>(url: string, payload: Record<string, unknown>): Promise<ApiResponse<T>> {
    const response = await http.put<ApiResponse<T>>(url, payload);

    return response.data;
}

export async function deleteData<T>(url: string): Promise<ApiResponse<T>> {
    const response = await http.delete<ApiResponse<T>>(url);

    return response.data;
}
