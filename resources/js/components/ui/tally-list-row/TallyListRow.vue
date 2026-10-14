<script setup lang="ts">
import type { AvatarGroupItem } from "@/components/ui/avatar"
import type { CategoryColor } from "@/lib/category"
import { computed } from "vue"
import { AvatarGroup } from "@/components/ui/avatar"
import { Badge } from "@/components/ui/badge"
import { CategoryGlyph } from "@/components/ui/category-glyph"
import { spendingToneClass } from "@/lib/spending"
import type { TallyRailCardAmount } from "@/components/ui/tally-rail"

export type TallyListRowAmount = TallyRailCardAmount

const props = withDefaults(
  defineProps<{
    name: string
    /** Trailing meta text next to the member stack. */
    meta?: string
    members?: AvatarGroupItem[]
    /** Desktop type pill, e.g. `Shared · 60/40`, `Private` (hidden below `md`). */
    typeLabel?: string
    /** Accent the type pill (shared tally). */
    typeShared?: boolean
    /** Desktop detail cell, e.g. `14 transactions` (hidden below `md`). */
    detail?: string
    amount?: number
    /** One line per currency; takes precedence over `amount`/`currency`/`decimals`. */
    amounts?: TallyListRowAmount[]
    /** Period of the amount, shown quietly under it (`total spent`). */
    amountCaption?: string
    /** Right-hand status line, e.g. `Settled`, `Luca owes 76.30`. */
    status?: string
    /** Shorter status shown below `sm` (640px) instead of `status`, e.g. `Owes you 76.30`. */
    statusShort?: string
    /** `pos` (owed to you) in `--pos`, `neutral` (you owe) in `--text-2`, `settled` in `--text-3`. */
    statusTone?: "pos" | "neutral" | "settled"
    /** Symbols of other currencies with a balance, appended quietly after the status (`$ £`). */
    statusExtra?: string
    currency?: string
    decimals?: number
    color: CategoryColor
    glyph?: string
  }>(),
  {
    currency: "€",
    decimals: 2,
    statusTone: "settled",
  },
)

const formatAmount = (line: TallyRailCardAmount): string =>
  Math.abs(line.amount).toLocaleString("en-US", {
    minimumFractionDigits: line.decimals,
    maximumFractionDigits: line.decimals,
  })

const amountLines = computed<TallyListRowAmount[]>(() => {
  if (props.amounts) {
    return props.amounts
  }

  return props.amount === undefined
    ? []
    : [{ amount: props.amount, currency: props.currency, decimals: props.decimals }]
})
</script>

<template>
  <div class="mb-2.5 flex cursor-pointer items-center gap-[14px] rounded-card bg-surface px-4 py-[15px] transition md:px-5 md:py-4 hover:bg-surface-2">
    <CategoryGlyph :color="color" size="lg" :glyph="glyph" />
    <span class="min-w-0 flex-1">
      <span class="block truncate text-[15.5px] font-semibold tracking-[-0.02em]">{{ name }}</span>
      <span class="mt-[3px] flex items-center gap-[7px] text-[12px] text-text-3">
        <AvatarGroup v-if="members && members.length" :items="members" size="xs" />
        <template v-if="meta">{{ meta }}</template>
      </span>
    </span>
    <span v-if="typeLabel" class="hidden w-40 shrink-0 justify-center md:flex">
      <Badge :variant="typeShared ? 'accent' : 'pill'">{{ typeLabel }}</Badge>
    </span>
    <span v-if="detail" class="hidden w-40 shrink-0 text-center text-[13px] text-text-2 md:block">{{ detail }}</span>
    <span class="shrink-0 text-right md:w-36">
      <span v-for="(line, index) in amountLines" :key="index" class="num block text-[16px] font-semibold whitespace-nowrap" :class="spendingToneClass(line.amount)">
        {{ line.currency }} {{ formatAmount(line) }}
      </span>
      <span v-if="amountCaption && amountLines.length" class="mt-[3px] ml-auto block text-[11px] text-text-3">{{ amountCaption }}</span>
      <span
        v-if="status"
        class="mt-[3px] ml-auto block max-w-36 text-[11px]"
        :class="{ 'font-medium text-pos': statusTone === 'pos', 'text-text-2': statusTone === 'neutral', 'text-text-3': statusTone === 'settled' }"
      >
        <template v-if="statusShort && statusShort !== status">
          <span class="sm:hidden">{{ statusShort }}</span>
          <span class="hidden sm:inline">{{ status }}</span>
        </template>
        <template v-else>{{ status }}</template>
        <span v-if="statusExtra" class="ml-1 text-text-3">· {{ statusExtra }}</span>
      </span>
    </span>
  </div>
</template>
