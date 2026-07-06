import { getData, postData } from '../../shared/api/http';
import type { ApiListParams, ApiResponse, PaginatedResponse } from '../../shared/api/types';
import type { EclatFilterParams, EclatResult, EclatRun, EclatRunDetail, EclatTransactionPeriod } from './types';

export function fetchEclatRuns(params: ApiListParams): Promise<PaginatedResponse<EclatRun>> {
    return getData<EclatRun[]>('/eclat/runs', params) as Promise<PaginatedResponse<EclatRun>>;
}

export function fetchEclatResults(params: ApiListParams): Promise<PaginatedResponse<EclatResult>> {
    return getData<EclatResult[]>('/eclat/results', params) as Promise<PaginatedResponse<EclatResult>>;
}

export function fetchEclatRun(id: number): Promise<ApiResponse<EclatRunDetail>> {
    return getData<EclatRunDetail>(`/eclat/runs/${id}`);
}

export function fetchEclatTransactionPeriod(): Promise<ApiResponse<EclatTransactionPeriod>> {
    return getData<EclatTransactionPeriod>('/eclat/transaction-period');
}

export function runEclatAnalysis(minSupport: number, minConfidence: number, filters: EclatFilterParams): Promise<ApiResponse<EclatRunDetail>> {
    return postData<EclatRunDetail>('/eclat/analyze', {
        min_support: minSupport,
        min_confidence: minConfidence,
        ...filters,
    });
}
