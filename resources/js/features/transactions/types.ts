import type { Snack } from '../snacks/types';

export interface TransactionDetail {
    id: number;
    transaction_id: number;
    snack_id: number;
    quantity: number;
    unit_price?: string | null;
    status: string;
    snack?: Snack;
}

export interface SalesTransaction {
    id: number;
    reference_no?: string | null;
    transaction_date: string;
    status: string;
    details: TransactionDetail[];
}

export interface TransactionPayload {
    reference_no?: string | null;
    transaction_date: string;
    status?: string;
    items: Array<{
        snack_id: number;
        quantity?: number;
        unit_price?: number | null;
    }>;
}
