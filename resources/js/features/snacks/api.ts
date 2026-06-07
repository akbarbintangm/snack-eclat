import { deleteData, getData, postData, putData } from '../../shared/api/http';
import type { ApiListParams, ApiResponse, PaginatedResponse } from '../../shared/api/types';
import type { Snack, SnackPayload } from './types';

export function fetchSnacks(params: ApiListParams): Promise<PaginatedResponse<Snack>> {
    return getData<Snack[]>('/snacks', params) as Promise<PaginatedResponse<Snack>>;
}

export function createSnack(payload: SnackPayload): Promise<ApiResponse<Snack>> {
    return postData<Snack>('/snacks', payload);
}

export function updateSnack(id: number, payload: SnackPayload): Promise<ApiResponse<Snack>> {
    return putData<Snack>(`/snacks/${id}`, payload);
}

export function deleteSnack(id: number): Promise<ApiResponse<null>> {
    return deleteData<null>(`/snacks/${id}`);
}
