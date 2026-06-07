import type { EclatResult, EclatRun, EclatStep } from '../eclat/types';

export interface DashboardSummary {
    snack_count: number;
    transaction_count: number;
    transaction_item_count: number;
    analysis_run_count: number;
    latest_run?: EclatRun | null;
    top_rule?: EclatResult | null;
    process_steps: EclatStep[];
}
