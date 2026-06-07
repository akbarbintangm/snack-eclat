export interface PageMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

export interface ApiResponse<T> {
    success: boolean;
    message: string;
    data: T;
    meta: Record<string, unknown>;
    errors?: unknown;
}

export interface PaginatedResponse<T> extends ApiResponse<T[]> {
    meta: PageMeta;
}

export interface ApiListParams {
    page?: number;
    per_page?: number;
    search?: string;
    status?: string;
    [key: string]: string | number | undefined;
}

export class ApiClientError extends Error {
    public readonly statusCode: number;
    public readonly errors: unknown;

    constructor(message: string, statusCode: number, errors?: unknown) {
        super(message);
        this.name = 'ApiClientError';
        this.statusCode = statusCode;
        this.errors = errors;
    }
}
