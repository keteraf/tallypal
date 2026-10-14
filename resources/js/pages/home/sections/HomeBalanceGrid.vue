<script setup lang="ts">
import { BalanceHero } from '@/components/ui/balance-hero';
import { InOutCards } from '@/components/ui/in-out-cards';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { BalanceShape, WeekHistory } from '../types';
import { deltaCaption, formatCurrencyTotals } from '../utils';

const props = defineProps<{
    balance: BalanceShape;
    weeklyHistory: WeekHistory[];
}>();

const sparkBars = computed(() => props.weeklyHistory.slice(-10).map((week) => week.amount));

const delta = computed(() =>
    props.balance.delta_percentage === null
        ? undefined
        : {
              tone: 'pos-neg' as const,
              direction: props.balance.delta_percentage < 0 ? ('down' as const) : ('up' as const),
              value: `${Math.abs(props.balance.delta_percentage)}%`,
              label: deltaCaption(props.balance.previous_period, locale.value, t),
          },
);

const { t, locale } = useI18n();
</script>

<template>
    <div
        class="grid grid-cols-1 items-end gap-[18px] rounded-card bg-surface px-5 py-[22px] min-[1101px]:grid-cols-[minmax(0,1fr)_auto] min-[1101px]:gap-8 sm:px-7 sm:py-[26px]"
    >
        <BalanceHero
            size="card"
            :label="t('home.balance.net')"
            :amount="balance.net"
            :currency="balance.currency_symbol"
            :decimals="balance.decimals"
            :bars="sparkBars"
            :delta="delta"
        >
            <template v-if="balance.other_currencies.length > 0" #note>
                <span class="num">{{ t('common.other_currencies', { amounts: formatCurrencyTotals(balance.other_currencies) }) }}</span>
            </template>
        </BalanceHero>
        <InOutCards
            inline
            :income="balance.income"
            :expense="balance.expense"
            :currency="balance.currency_symbol"
            :decimals="balance.decimals"
            :in-label="t('home.balance.in')"
            :out-label="t('home.balance.out')"
        />
    </div>
</template>
