import type { PrimaryCurrency } from '@/lib/spending';
import type { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/** What the server falls back to for a user with no tallies yet (`TransactionStatsRepository::primaryCurrency`). */
const FALLBACK_CURRENCY: PrimaryCurrency = { symbol: '€', code: 'EUR', decimals: 2 };

/** The user's primary currency from the shared page props. */
export function usePrimaryCurrency() {
    const page = usePage<AppPageProps>();

    return computed<PrimaryCurrency>(() => page.props.primary_currency ?? FALLBACK_CURRENCY);
}
