import { getData } from '../../shared/api/http';
import type { ApiListParams, PaginatedResponse } from '../../shared/api/types';
import type { Recommendation } from './types';

export function fetchRecommendations(params: ApiListParams): Promise<PaginatedResponse<Recommendation>> {
    return getData<Recommendation[]>('/reports/recommendations', params) as Promise<PaginatedResponse<Recommendation>>;
}
