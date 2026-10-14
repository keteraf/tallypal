<script setup lang="ts">
import type { CategoryColor } from "@/lib/category"
import { computed } from "vue"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { MiniBar } from "@/components/ui/mini-bar"

const props = withDefaults(
  defineProps<{
    name: string
    amount: number
    /** Share of the split, 0–100. */
    percentage: number
    color: CategoryColor
    initials?: string
    currency?: string
    decimals?: number
  }>(),
  {
    currency: "€",
    decimals: 0,
  },
)

const formattedAmount = computed(() =>
  Math.abs(props.amount).toLocaleString("en-US", {
    minimumFractionDigits: props.decimals,
    maximumFractionDigits: props.decimals,
  }),
)
</script>

<template>
  <div class="flex items-center gap-3 py-3">
    <Avatar size="sm">
      <AvatarFallback :color="color">{{ initials }}</AvatarFallback>
    </Avatar>
    <span class="min-w-0 flex-1">
      <div class="text-[14px] font-medium">{{ name }}</div>
      <MiniBar class="mt-[7px]" :value="percentage" :color="color" />
    </span>
    <span class="shrink-0 text-right">
      <div class="num text-[14px] font-semibold">{{ currency }} {{ formattedAmount }}</div>
      <div class="mt-1 font-mono text-[11px] text-text-3">{{ percentage }}%</div>
    </span>
  </div>
</template>
