<script setup lang="ts">
import { ArrowDown, ArrowUp } from 'lucide-vue-next';
import type { BalanceShape } from '../types';
import { splitNumber } from '../utils';

const props = defineProps<{
    balance: BalanceShape;
    currentMonthLabel: string;
}>();

const net = (value: number) => splitNumber(value, props.balance.decimals);
</script>

<template>
    <div class="mb-9 grid grid-cols-1 overflow-hidden rounded-sm border border-rule md:grid-cols-3">
        <div class="border-b border-rule-2 px-7 py-7 md:border-r md:border-b-0">
            <div class="eyebrow mb-3.5">{{ $t('home.balance.net') }}</div>
            <div class="font-serif text-[48px] leading-none tracking-[-0.02em]">
                <span class="mr-1 align-super text-[24px] text-muted-foreground">{{ balance.currency_symbol }}</span>
                {{ net(balance.net).whole }}<span class="text-[32px] text-muted-foreground">.{{ net(balance.net).decimal }}</span>
            </div>
            <div class="mt-3 flex items-center gap-2 font-mono text-[12px] text-muted-foreground">
                <span :class="balance.net >= 0 ? 'text-pos' : 'text-neg'">
                    <component :is="balance.net >= 0 ? ArrowUp : ArrowDown" class="inline size-3.5" />
                </span>
                {{ $t('home.balance.net_helper', { month: currentMonthLabel }) }}
            </div>
        </div>
        <div class="border-b border-rule-2 px-7 py-7 md:border-r md:border-b-0">
            <div class="eyebrow mb-3.5">{{ $t('home.balance.in') }}</div>
            <div class="font-serif text-[36px] leading-none tracking-[-0.02em]">
                <span class="mr-1 align-super text-[20px] text-muted-foreground">{{ balance.currency_symbol }}</span>
                {{ net(balance.income).whole }}<span class="text-muted-foreground">.{{ net(balance.income).decimal }}</span>
            </div>
            <div class="mt-3 flex items-center gap-2 font-mono text-[12px] text-muted-foreground">
                <span class="text-pos"><ArrowUp class="inline size-3.5" /></span>
                {{ $t('home.balance.in_helper') }}
            </div>
        </div>
        <div class="px-7 py-7">
            <div class="eyebrow mb-3.5">{{ $t('home.balance.out') }}</div>
            <div class="font-serif text-[36px] leading-none tracking-[-0.02em]">
                <span class="mr-1 align-super text-[20px] text-muted-foreground">{{ balance.currency_symbol }}</span>
                {{ net(balance.expense).whole }}<span class="text-muted-foreground">.{{ net(balance.expense).decimal }}</span>
            </div>
            <div class="mt-3 flex items-center gap-2 font-mono text-[12px] text-muted-foreground">
                <span class="text-neg"><ArrowDown class="inline size-3.5" /></span>
                {{ $t('home.balance.out_helper') }}
            </div>
        </div>
    </div>
</template>
