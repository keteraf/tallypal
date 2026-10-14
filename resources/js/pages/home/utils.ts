import type { BalanceCurrency, CurrencyTotal, MyBalance } from './types';

export function formatCurrency(value: number, decimals: number): string {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(Math.abs(value));
}

/**
 * One total per other currency, each in its own symbol (`$ 27.50 · £ −3.00`). Amounts in different
 * currencies are never added together or converted (O-04).
 */
export function formatCurrencyTotals(totals: CurrencyTotal[]): string {
    return totals
        .map((total) => `${total.currency_symbol} ${total.amount < 0 ? '−' : ''}${formatCurrency(total.amount, total.decimals)}`)
        .join(' · ');
}

/** Minor units of a B-04 amount in its currency, formatted without a symbol (`7630` → `76.30`). */
export function formatBalanceAmount(amount: number, currency: BalanceCurrency): string {
    return formatCurrency(amount / 10 ** currency.decimals, currency.decimals);
}

export type GreetingPeriod = 'morning' | 'afternoon' | 'evening';

/** The part of the day for the Home greeting, from the viewer's local hour (0–23): 05–11, 12–17, else evening. */
export function greetingPeriod(hour: number): GreetingPeriod {
    if (hour >= 5 && hour < 12) {
        return 'morning';
    }

    return hour >= 12 && hour < 18 ? 'afternoon' : 'evening';
}

export type BalanceTone = 'pos' | 'neutral' | 'settled';

export interface BalanceStatus {
    label: string;
    /** Name-less form for narrow screens (`Owes you 76.30`); equals `label` when there is no single name to drop. */
    shortLabel: string;
    tone: BalanceTone;
    /** Symbols of the other currencies with a non-zero balance (`$ £`), shown quietly after the label. */
    otherSymbols: string;
}

type Translate = (key: string, named?: Record<string, unknown>, plural?: number) => string;

/**
 * The tally's status text (F-033, F-050), from its first `my_balance` entry (most-used currency):
 * "Luca owes 76.30", "2 people owe you 76.30", "You owe Luca 20.00", "Settled". Other currencies are
 * never summed or converted; only their symbols follow when their balance is not zero (O-04).
 */
export function balanceStatus(balances: MyBalance[], t: Translate): BalanceStatus {
    const [first] = balances;
    const otherSymbols = balances
        .slice(1)
        .filter((balance) => balance.amount !== 0)
        .map((balance) => balance.currency.symbol)
        .join(' ');

    if (first === undefined || first.direction === 'settled') {
        const settled = t('tallies.status.settled');

        return { label: settled, shortLabel: settled, tone: 'settled', otherSymbols };
    }

    const amount = formatBalanceAmount(first.amount, first.currency);
    const count = Math.max(first.counterparties_count, 1);
    const named = first.counterparty_name !== null && first.counterparties_count === 1;

    if (first.direction === 'owed_to_me') {
        const label = named
            ? t('tallies.status.owes_you', { name: first.counterparty_name, amount })
            : t('tallies.status.owe_you_many', { count, amount }, count);

        return {
            label,
            shortLabel: named ? t('tallies.status.short_owes_you', { amount }) : label,
            tone: 'pos',
            otherSymbols,
        };
    }

    const label = named
        ? t('tallies.status.you_owe', { name: first.counterparty_name, amount })
        : t('tallies.status.you_owe_many', { count, amount }, count);

    return {
        label,
        shortLabel: named ? t('tallies.status.short_you_owe', { amount }) : label,
        tone: 'neutral',
        otherSymbols,
    };
}

/**
 * The "vs Mar 1–14" caption of a period-to-date delta pill (F-022), shared by Home and Stats:
 * one day reads "vs Mar 1", a span "vs Mar 1–14". `null` when there is no previous period.
 */
export function deltaCaption(period: { start: string; end: string } | null, locale: string, t: Translate): string | undefined {
    if (period === null) {
        return undefined;
    }

    const start = new Date(`${period.start}T00:00:00Z`);
    const end = new Date(`${period.end}T00:00:00Z`);
    const month = new Intl.DateTimeFormat(locale, { month: 'short', timeZone: 'UTC' }).format(start).replace('.', '');
    const params = { month, start: start.getUTCDate(), end: end.getUTCDate() };

    return start.getTime() === end.getTime() ? t('home.balance.delta_caption_single', params) : t('home.balance.delta_caption', params);
}
