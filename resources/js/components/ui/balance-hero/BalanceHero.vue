<script setup lang="ts">
import { computed } from "vue"
import TrendBadge from "@/components/ui/trend-badge/TrendBadge.vue"

const props = withDefaults(
  defineProps<{
    label?: string
    amount: number
    /** Currency symbol shown before the amount. */
    currency?: string
    decimals?: number
    /** `card`: padding-free, larger type, for use inside a parent card (Home hero). */
    size?: "default" | "card"
    /** Optional sparkline bars (oldest first); the last bar is the current one. Card size only. */
    bars?: number[]
    /** Optional month-over-month trend badge; its `tone` says whether the direction is good or bad news. */
    delta?: { tone: "pos-neg" | "neutral"; direction?: "up" | "down"; value: string | number; label?: string }
  }>(),
  {
    currency: "€",
    decimals: 2,
    size: "default",
  },
)

const absolute = computed(() => Math.abs(props.amount))
const intPart = computed(() => Math.trunc(absolute.value).toLocaleString("en-US"))
const fracPart = computed(() => {
  if (props.decimals <= 0) {
    return ""
  }

  return "." + (absolute.value - Math.trunc(absolute.value)).toFixed(props.decimals).slice(2)
})
const barHeights = computed(() => {
  const values = props.bars ?? []
  const max = Math.max(...values, 0)

  return values.map((value) => (max > 0 ? Math.max((Math.abs(value) / max) * 100, 6) : 6))
})
const sign = computed(() => (props.amount < 0 ? "−" : ""))
</script>

<template>
  <div :class="size === 'card' ? undefined : 'pt-[14px] pb-[26px]'">
    <div v-if="label || $slots.label" class="flex items-center gap-1.5 text-[13px] text-text-3" :class="size === 'card' ? 'mb-2.5' : 'mb-2'">
      <slot name="label">{{ label }}</slot>
    </div>
    <div
      class="font-mono tabular flex items-baseline font-semibold leading-none tracking-[-0.045em]"
      :class="size === 'card' ? 'text-[48px] sm:text-[64px]' : 'text-[52px]'"
    >
      <span class="font-medium text-text-3" :class="size === 'card' ? 'mr-2 text-[26px] sm:text-[34px]' : 'mr-1.5 text-[30px]'">{{ currency }}</span>
      <span>{{ sign }}{{ intPart }}</span>
      <span class="font-medium text-text-3">{{ fracPart }}</span>
    </div>
    <div v-if="$slots.note" class="mt-2.5 text-[12px] text-text-3">
      <slot name="note" />
    </div>
    <div v-if="delta" class="mt-[14px]">
      <TrendBadge :tone="delta.tone" :direction="delta.direction" :value="delta.value" :label="delta.label" />
    </div>
    <div
      v-if="size === 'card' && barHeights.length"
      class="mt-4 flex h-14 items-end gap-1 sm:mt-[22px] sm:h-[72px]"
      aria-hidden="true"
    >
      <i
        v-for="(height, index) in barHeights"
        :key="index"
        class="flex-1 rounded-spark"
        :class="index === barHeights.length - 1 ? 'bg-accent' : 'bg-surface-3'"
        :style="{ height: `${height}%` }"
      />
    </div>
  </div>
</template>
