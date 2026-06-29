import { deleteData, getData, postData, postFormData, putData } from '../../shared/api/http';
import type { ApiListParams, ApiResponse, PaginatedResponse } from '../../shared/api/types';
import type { SalesTransaction, TransactionImportSummary, TransactionPayload } from './types';

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

export function importTransactions(file: File): Promise<ApiResponse<TransactionImportSummary>> {
    const formData = new FormData();
    formData.append('file', file);

    return postFormData<TransactionImportSummary>('/transactions/import', formData);
}
