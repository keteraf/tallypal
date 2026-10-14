import type { CategoryColor } from '@/lib/category';
import type { CurrencyTotal, WeekHistory } from '@/pages/home/types';

export type StatsRangeValue = 'week' | 'month' | 'year';

/** A single bar in the Stats chart — same shape for daily/weekly/monthly buckets. */
export type StatsSeriesPoint = WeekHistory;

export type StatsCurrencyTotal = CurrencyTotal;

/** The span the previous period covers (`Y-m-d`), for the "vs <period>" caption (B-09). */
export interface StatsPeriod {
    start: string;
    end: string;
}

/** Debts owed to the user in one currency (B-04): `amount_minor` is integer minor units (divide by 10^decimals). */
export interface StatsOwedCurrency {
    amount_minor: number;
    currency_symbol: string;
    currency_code: string;
    decimals: number;
}

/** Open debts owed to the user in the KPI currency (B-04), and the debts in every other currency, listed apart (O-04). */
export interface StatsOwedToMe {
    amount_minor: number;
    open_balances: number;
    other_currencies: StatsOwedCurrency[];
}

/** One All activity row, in its own currency (never the KPI one); `amount` is in major units. */
export interface StatsActivityItem {
    uuid: string;
    title: string;
    type: 'income' | 'expense';
    amount: number;
    transaction_date: string;
    category_id: number | null;
    category_name: string | null;
    category_code: string;
    category_slug: string | null;
    category_color: CategoryColor | null;
    tally_uuid: string;
    tally_name: string;
    is_shared: boolean;
    currency_symbol: string;
    currency_code: string;
    decimals: number;
    /** Lets the row feed `DataTable`. */
    [key: string]: unknown;
}

/** A Stats tally filter option. */
export interface StatsTallyOption {
    uuid: string;
    name: string;
    /** More than one member; a filtered private tally reads "Private tally" on the shared-portion KPI. */
    is_shared: boolean;
}
