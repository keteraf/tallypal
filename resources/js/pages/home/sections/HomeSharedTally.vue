<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { SharedTally } from '../types';
import { dotClass, formatCurrency, memberClass, splitName } from '../utils';

defineProps<{
    sharedTally: SharedTally;
    currencySymbol: string;
    decimals: number;
}>();
</script>

<template>
    <section class="overflow-hidden rounded-sm border border-rule">
        <div class="px-7 py-6">
            <div class="mb-3.5 flex items-center gap-3 font-mono text-[11px] tracking-[0.06em] text-muted-foreground uppercase">
                <span class="inline-block size-1.5 rounded-full bg-brand" />
                {{ $t('home.shared.eyebrow', { count: sharedTally.members.length }) }}
            </div>
            <h3 class="m-0 mb-5 font-serif text-[28px] leading-[1.05] tracking-[-0.015em]">
                <template v-if="splitName(sharedTally.name).lead">{{ splitName(sharedTally.name).lead }} </template>
                <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ splitName(sharedTally.name).em }}</em>
            </h3>
            <div class="font-mono text-[11px] tracking-[0.06em] text-muted-foreground uppercase">
                {{ $t('home.shared.total') }} · {{ currencySymbol }} {{ formatCurrency(sharedTally.total_spent, decimals) }}
            </div>
        </div>
        <div
            v-for="(member, idx) in sharedTally.members"
            :key="member.name + idx"
            class="grid grid-cols-[auto_1fr_auto] items-center gap-3.5 border-t border-rule-2 px-7 py-3.5 text-sm"
        >
            <span class="flex items-center gap-2.5 text-foreground">
                <span
                    :class="[
                        'inline-flex size-7 items-center justify-center rounded-full border border-current/30 font-mono text-[11px] font-medium',
                        memberClass(idx),
                    ]"
                >
                    {{ member.initials }}
                </span>
                {{ member.name }}
            </span>
            <span class="relative h-1 overflow-hidden rounded-full bg-rule-2">
                <span
                    class="absolute top-0 bottom-0 left-0 rounded-full"
                    :class="dotClass(idx)"
                    :style="{ width: `${Math.min(100, member.percentage)}%` }"
                />
            </span>
            <span class="font-mono text-[11px] tracking-[0.06em] text-muted-foreground">
                {{ currencySymbol }} {{ formatCurrency(member.amount, decimals) }}
            </span>
        </div>
        <Link
            :href="route('tallies.transactions.index', sharedTally.uuid)"
            class="flex items-center justify-between border-t border-rule px-7 py-4 font-mono text-[12px] tracking-[0.04em] text-muted-foreground uppercase transition-colors hover:text-foreground"
        >
            <span class="font-sans text-sm tracking-normal text-foreground normal-case">{{ $t('home.shared.view_full') }}</span>
            <span class="text-brand">→</span>
        </Link>
    </section>
</template>
