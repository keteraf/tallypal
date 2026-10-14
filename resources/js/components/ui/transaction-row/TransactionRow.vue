<script setup lang="ts">
import type { GlyphColor } from "@/lib/category"
import { computed } from "vue"
import { Badge } from "@/components/ui/badge"
import { CategoryGlyph } from "@/components/ui/category-glyph"

const props = withDefaults(
  defineProps<{
    title: string
    /** Meta line (date / payer). */
    subtitle?: string
    /** Optional tag; `shared` renders the accent variant. */
    pill?: { label: string; variant?: "default" | "shared" }
    amount: number
    type?: "income" | "expense" | "neutral"
    /** Currency symbol; omitted by default (source shows bare amounts). */
    currencySymbol?: string
    decimals?: number
    color: GlyphColor
    glyph?: string
    clickable?: boolean
  }>(),
  {
    type: "expense",
    decimals: 2,
  },
)

const sign = computed(() => {
  if (props.type === "income") {
    return "+ "
  }

  if (props.type === "expense") {
    return "− "
  }

  return ""
})

const formatted = computed(() => {
  const number = Math.abs(props.amount).toLocaleString("en-US", {
    minimumFractionDigits: props.decimals,
    maximumFractionDigits: props.decimals,
  })
  const prefix = props.currencySymbol ? `${props.currencySymbol} ` : ""

  return `${sign.value}${prefix}${number}`
})
</script>

<template>
  <div
    class="flex items-center gap-[13px] py-3"
    :class="clickable ? '-mx-4 cursor-pointer px-4 transition-colors hover:bg-surface-2' : ''"
  >
    <CategoryGlyph :color="color" size="md" :glyph="glyph" />
    <span class="min-w-0 flex-1">
      <span class="block truncate text-[14.5px] font-medium tracking-[-0.015em]">{{ title }}</span>
      <span class="mt-0.5 flex flex-wrap items-center gap-x-[5px] gap-y-1 text-[12px] text-text-3">
        <span v-if="subtitle" class="whitespace-nowrap">{{ subtitle }}</span>
        <Badge v-if="pill" :variant="pill.variant === 'shared' ? 'accent' : 'pill'" class="whitespace-nowrap">{{ pill.label }}</Badge>
      </span>
    </span>
    <span
      class="num shrink-0 text-[15px] font-semibold"
      :class="type === 'income' ? 'text-pos' : ''"
    >
      {{ formatted }}
    </span>
  </div>
</template>
