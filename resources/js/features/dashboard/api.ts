import { getData } from '../../shared/api/http';
import type { ApiResponse } from '../../shared/api/types';
import type { DashboardSummary } from './types';

export function fetchDashboardSummary(): Promise<ApiResponse<DashboardSummary>> {
    return getData<DashboardSummary>('/reports/summary');
}
