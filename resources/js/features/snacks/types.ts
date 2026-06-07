export interface Snack {
    id: number;
    name: string;
    status: string;
    created_at?: string;
    updated_at?: string;
}

export interface SnackPayload {
    name: string;
    status?: string;
}
