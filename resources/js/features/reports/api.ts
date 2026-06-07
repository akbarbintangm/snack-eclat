import { getData } from '../../shared/api/http';
import type { ApiResponse } from '../../shared/api/types';
import type { Recommendation } from './types';

export function fetchRecommendations(limit = 10): Promise<ApiResponse<Recommendation[]>> {
    return getData<Recommendation[]>('/reports/recommendations', { limit });
}
