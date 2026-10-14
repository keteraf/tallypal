import type { CategoryColor } from '@/lib/category';

export interface PrimaryTally {
    uuid: string;
    name: string;
}

/** A total in one currency, shown apart from the primary-currency aggregates (O-04). */
export interface CurrencyTotal {
    amount: number;
    currency_symbol: string;
    currency_code: string;
    decimals: number;
}

/** `amount` is the month's expense total: money spent, per currency (owner decision, FP-01). */
export type TallyMonthlyTotal = CurrencyTotal;

/** Currency of a B-04 balance; amounts in it are integer minor units (divide by 10^decimals). */
export interface BalanceCurrency {
    code: string;
    symbol: string;
    decimals: number;
}

/** The user's net position in one currency of one tally (B-04, O-04): never netted across currencies. */
export interface MyBalance {
    currency: BalanceCurrency;
    /** Signed, integer minor units: + owed to the user, − the user owes, 0 settled. */
    amount: number;
    direction: 'owed_to_me' | 'i_owe' | 'settled';
    /** Set only when exactly one person is on the other side. */
    counterparty_name: string | null;
    counterparties_count: number;
}

/** The single debt to settle first (Home "Settle", B-04); `amount` is integer minor units. */
export interface SettleSuggestion {
    tally_uuid: string;
    counterparty_name: string;
    amount: number;
    currency: BalanceCurrency;
}

export interface TallySummary {
    uuid: string;
    name: string;
    color: string | null;
    glyph: string | null;
    member_count: number;
    /** Up to three members, owner first; initials only (never emails). */
    members_preview: { initials: string; color: CategoryColor }[];
    /** e.g. `60 / 40`; null for a solo tally or an equal split. */
    split_label: string | null;
    monthly_totals: TallyMonthlyTotal[];
    /** One entry per currency the tally has transactions in, most-used first (B-04). */
    my_balance: MyBalance[];
    tally_id: number;
}

export interface BalanceShape {
    net: number;
    income: number;
    expense: number;
    currency_symbol: string;
    currency_code: string;
    decimals: number;
    /** Net of each other currency with activity this month; never added to `net`. */
    other_currencies: CurrencyTotal[];
    /** Net of the whole previous month, in `currency_code` (B-03). */
    previous_net: number;
    /** The span `previous_net` covers (`Y-m-d`), for the delta caption; null on the empty payload. */
    previous_period: { start: string; end: string } | null;
    /** `(net - previous_net) / |previous_net| × 100`, one decimal; null when `previous_net` is 0. Its sign is the arrow direction. */
    delta_percentage: number | null;
}

export interface RecentTransaction {
    uuid: string;
    title: string;
    type: 'income' | 'expense';
    amount: number;
    transaction_date: string;
    category_id: number | null;
    category_name: string | null;
    category_code: string;
    tally_name: string;
    is_shared: boolean;
    currency_symbol: string;
}

export interface WeekHistory {
    label: string;
    amount: number;
    is_current: boolean;
}

/** A By category row: the top five plus one "Other" remainder, each with its bar fill (0–100) already scaled by the server (FP-04, FP-09). */
export interface CategoryBreakdown {
    name: string;
    amount: number;
    percentage: number;
    bar_fill: number;
    is_other: boolean;
}
