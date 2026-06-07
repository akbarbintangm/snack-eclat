import { deleteData, getData, postData, putData } from '../../shared/api/http';
import type { ApiListParams, ApiResponse, PaginatedResponse } from '../../shared/api/types';
import type { SalesTransaction, TransactionPayload } from './types';

export function fetchTransactions(params: ApiListParams): Promise<PaginatedResponse<SalesTransaction>> {
    return getData<SalesTransaction[]>('/transactions', params) as Promise<PaginatedResponse<SalesTransaction>>;
}

export function createTransaction(payload: TransactionPayload): Promise<ApiResponse<SalesTransaction>> {
    return postData<SalesTransaction>('/transactions', payload as unknown as Record<string, unknown>);
}

export function updateTransaction(id: number, payload: TransactionPayload): Promise<ApiResponse<SalesTransaction>> {
    return putData<SalesTransaction>(`/transactions/${id}`, payload as unknown as Record<string, unknown>);
}

export function deleteTransaction(id: number): Promise<ApiResponse<null>> {
    return deleteData<null>(`/transactions/${id}`);
}
