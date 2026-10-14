<script setup lang="ts">
import { computed } from "vue"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<{
    /** Who owes whom. `settled` renders a neutral "all settled" line. */
    direction?: "owes-you" | "you-owe" | "settled"
    name?: string
    amount?: number
    currency?: string
    decimals?: number
    class?: string
  }>(),
  {
    direction: "owes-you",
    currency: "€",
    decimals: 2,
  },
)

const bannerStyle = { background: "color-mix(in oklch, var(--accent) 15%, transparent)" }

const formattedAmount = computed(() =>
  props.amount === undefined
    ? ""
    : Math.abs(props.amount).toLocaleString("en-US", {
        minimumFractionDigits: props.decimals,
        maximumFractionDigits: props.decimals,
      }),
)
</script>

<template>
  <div :class="cn('mt-[14px] flex items-center justify-between gap-3 rounded-card px-5 py-[18px]', props.class)" :style="bannerStyle">
    <span class="min-w-0 flex-1 text-sm font-medium leading-[1.4] tracking-[-0.01em]">
      <!-- Callers with i18n copy pass their own translated text via #message. -->
      <slot name="message">
        <template v-if="direction === 'settled'">All settled up</template>
        <template v-else-if="direction === 'owes-you'">
          <b class="font-semibold">{{ name }}</b> owes you
          <span class="num font-semibold text-primary">{{ currency }} {{ formattedAmount }}</span>
        </template>
        <template v-else>
          You owe <b class="font-semibold">{{ name }}</b>{{ " " }}
          <span class="num font-semibold text-primary">{{ currency }} {{ formattedAmount }}</span>
        </template>
      </slot>
      <span v-if="$slots.detail" class="mt-0.5 block text-[12.5px] font-normal text-text-3">
        <slot name="detail" />
      </span>
    </span>
    <slot />
  </div>
</template>
