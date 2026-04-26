export interface PrimaryTally {
    uuid: string;
    name: string;
}

export interface TallySummary {
    uuid: string;
    name: string;
    member_count: number;
    monthly_total: number;
}

export interface BalanceShape {
    net: number;
    income: number;
    expense: number;
    currency_symbol: string;
    currency_code: string;
    decimals: number;
}

export interface RecentTransaction {
    uuid: string;
    title: string;
    type: 'income' | 'expense';
    amount: number;
    transaction_date: string;
    category_name: string | null;
    category_code: string;
    tally_name: string;
    currency_symbol: string;
}

export interface WeekHistory {
    label: string;
    amount: number;
    is_current: boolean;
}

export interface CategoryBreakdown {
    name: string;
    amount: number;
    percentage: number;
}

export interface SharedTallyMember {
    name: string;
    initials: string;
    amount: number;
    percentage: number;
}

export interface SharedTally {
    uuid: string;
    name: string;
    total_spent: number;
    members: SharedTallyMember[];
}
