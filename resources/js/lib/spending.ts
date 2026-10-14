import type { CurrencyTotal } from '@/pages/home/types';

/** The user's primary currency: default currency, else the most used (shared page prop `primary_currency`). */
export interface PrimaryCurrency {
    symbol: string;
    code: string;
    decimals: number;
}

/**
 * What a tally with no spending shows: one zero line in the user's primary currency, since there is
 * no transaction to take a currency from. The sidebar, the Home card and the Tallies row all use it,
 * so an empty tally reads "0.00" on every surface (FP-14).
 */
export function zeroSpending(currency: PrimaryCurrency): CurrencyTotal {
    return { amount: 0, currency_symbol: currency.symbol, currency_code: currency.code, decimals: currency.decimals };
}

/** A spending figure is `--text`; one with no spending is quiet, `--text-3`, on every surface (FP-14). */
export function spendingToneClass(amount: number): 'text-text' | 'text-text-3' {
    return amount === 0 ? 'text-text-3' : 'text-text';
}
