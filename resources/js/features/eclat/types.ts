export interface EclatResult {
    id: number;
    eclat_run_id: number;
    combination_item: string;
    antecedent_items: string[];
    consequent_items: string[];
    transaction_count: number;
    support: number;
    confidence: number;
    lift_ratio?: number | null;
    tid_list?: number[];
    recommendation?: string | null;
    status: string;
}

export interface EclatRun {
    id: number;
    run_code: string;
    min_support: number;
    min_confidence: number;
    total_transactions: number;
    total_items: number;
    frequent_itemset_count: number;
    rule_count: number;
    execution_time_ms?: number | null;
    tid_list?: Record<string, number[]>;
    frequent_itemsets?: EclatItemset[];
    status: string;
    results?: EclatResult[];
    created_at?: string;
}

export interface EclatItemset {
    key: string;
    items: string[];
    label: string;
    transaction_count: number;
    support: number;
    tid_list: number[];
}

export interface EclatStep {
    order: number;
    name: string;
    description: string;
    metric: string;
    status: string;
}

export interface EclatRunDetail {
    run: EclatRun;
    steps: EclatStep[];
}
