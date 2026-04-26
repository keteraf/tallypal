<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PrimaryTally, RecentTransaction } from '../types';
import { formatCurrency } from '../utils';

const props = defineProps<{
    transactions: RecentTransaction[];
    primaryTally: PrimaryTally | null;
    decimals: number;
}>();

const txFallbackUrl = (uuid?: string) => {
    if (uuid) return route('tallies.transactions.index', uuid);
    if (props.primaryTally) return route('tallies.transactions.index', props.primaryTally.uuid);
    return '/tallies';
};
</script>

<template>
    <section class="overflow-hidden rounded-sm border border-rule">
        <div class="flex items-baseline justify-between border-b border-rule-2 px-7 py-5">
            <span class="font-serif text-xl text-foreground italic">{{ $t('home.recent.title') }}</span>
            <Link
                v-if="primaryTally"
                :href="route('tallies.transactions.index', primaryTally.uuid)"
                class="font-mono text-[11px] tracking-[0.06em] text-muted-foreground uppercase transition-colors hover:text-foreground"
            >
                {{ $t('home.recent.view_all') }} →
            </Link>
        </div>
        <div v-if="transactions.length === 0" class="px-7 py-12 text-center text-muted-foreground">
            {{ $t('home.recent.empty') }}
        </div>
        <Link
            v-for="tx in transactions"
            :key="tx.uuid"
            :href="txFallbackUrl()"
            class="grid grid-cols-[36px_1fr_auto_auto] items-center gap-4 border-b border-rule-2 px-7 py-4 transition-colors last:border-b-0 hover:bg-paper-2"
        >
            <span
                class="inline-flex size-8 items-center justify-center rounded-full border border-rule bg-paper-2 font-mono text-[11px] tracking-[0.04em] text-muted-foreground"
            >
                {{ tx.category_code }}
            </span>
            <span class="min-w-0">
                <span class="block truncate text-foreground">{{ tx.title }}</span>
                <span class="mt-1 block truncate font-mono text-[11px] tracking-[0.04em] text-muted-foreground">
                    {{ tx.transaction_date }} · {{ tx.category_name ?? tx.type }}
                </span>
            </span>
            <span class="hidden max-w-[120px] truncate font-mono text-[11px] tracking-[0.04em] text-muted-foreground sm:inline">{{
                tx.tally_name
            }}</span>
            <span class="tabular font-mono text-[15px] font-medium" :class="tx.type === 'income' ? 'text-pos' : 'text-foreground'">
                {{ tx.type === 'income' ? '+' : '−' }} {{ tx.currency_symbol }} {{ formatCurrency(tx.amount, decimals) }}
            </span>
        </Link>
    </section>
</template>
