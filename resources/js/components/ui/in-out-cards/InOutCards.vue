<script setup lang="ts">
import { computed } from "vue"
import { Dot } from "@/components/ui/dot"

const props = withDefaults(
  defineProps<{
    income: number
    expense: number
    currency?: string
    /** Fix the fraction digits shown (defaults to `toLocaleString`'s own 0–3 range). */
    decimals?: number
    inLabel?: string
    outLabel?: string
    /** `inline`: card-less In/Out pair for use inside a parent card (Home hero). */
    inline?: boolean
  }>(),
  {
    currency: "€",
    inLabel: "In",
    outLabel: "Out",
  },
)

const format = (value: number): string =>
  props.decimals === undefined
    ? Math.abs(value).toLocaleString("en-US")
    : Math.abs(value).toLocaleString("en-US", { minimumFractionDigits: props.decimals, maximumFractionDigits: props.decimals })

const incomeText = computed(() => format(props.income))
const expenseText = computed(() => format(props.expense))
</script>

<template>
  <div v-if="inline" class="flex gap-5 sm:gap-7">
    <div>
      <div class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-text-3">
        <Dot color="pos" />{{ inLabel }}
      </div>
      <div class="num mt-2 text-[20px] font-semibold text-pos sm:text-[26px]">{{ currency }} {{ incomeText }}</div>
    </div>
    <div>
      <div class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-text-3">
        <Dot color="text-3" />{{ outLabel }}
      </div>
      <div class="num mt-2 text-[20px] font-semibold sm:text-[26px]">{{ currency }} {{ expenseText }}</div>
    </div>
  </div>
  <div v-else class="grid grid-cols-2 gap-2.5">
    <div class="rounded-card bg-surface px-4 py-[15px]">
      <div class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-text-3">
        <Dot color="pos" />{{ inLabel }}
      </div>
      <div class="num mt-[9px] text-[22px] font-semibold text-pos">{{ currency }} {{ incomeText }}</div>
    </div>
    <div class="rounded-card bg-surface px-4 py-[15px]">
      <div class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-text-3">
        <Dot color="text-3" />{{ outLabel }}
      </div>
      <div class="num mt-[9px] text-[22px] font-semibold">{{ currency }} {{ expenseText }}</div>
    </div>
  </div>
</template>
