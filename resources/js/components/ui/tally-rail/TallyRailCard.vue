<script setup lang="ts">
import type { AvatarGroupItem } from "@/components/ui/avatar"
import type { CategoryColor } from "@/lib/category"
import { computed } from "vue"
import { Plus } from "lucide-vue-next"
import { AvatarGroup } from "@/components/ui/avatar"
import { Badge } from "@/components/ui/badge"
import { CategoryGlyph } from "@/components/ui/category-glyph"
import { spendingToneClass } from "@/lib/spending"

export interface TallyRailCardAmount {
  amount: number
  currency: string
  decimals: number
}

const props = withDefaults(
  defineProps<{
    /** `default` = tally card; `new` = dashed "add" affordance. */
    variant?: "default" | "new"
    name?: string
    /** Sub-line, e.g. `3 members · 8 days`. */
    meta?: string
    members?: AvatarGroupItem[]
    amount?: number
    /** One line per currency; takes precedence over `amount`/`currency`/`decimals`. */
    amounts?: TallyRailCardAmount[]
    /** Period of the amount, shown quietly under it (`spent in Oct`). */
    amountCaption?: string
    currency?: string
    decimals?: number
    color?: CategoryColor
    glyph?: string
    /** Footer status pill, e.g. `Luca owes 76.30`, `Settled`; shown from `md` only (the design hides it on phones). */
    status?: string
    /** `pos` = owed to you (positive chip), `neutral` = you owe, `settled` = quiet chip. */
    statusTone?: "pos" | "neutral" | "settled"
    /** Symbols of other currencies with a balance, appended quietly after the status (`$ £`). */
    statusExtra?: string
    /** Label for the `new` variant. */
    label?: string
  }>(),
  {
    variant: "default",
    currency: "€",
    decimals: 2,
    color: "c1",
    statusTone: "settled",
    label: "New",
  },
)

const formatAmount = (line: TallyRailCardAmount): string =>
  Math.abs(line.amount).toLocaleString("en-US", {
    minimumFractionDigits: line.decimals,
    maximumFractionDigits: line.decimals,
  })

const amountLines = computed<TallyRailCardAmount[]>(() => {
  if (props.amounts) {
    return props.amounts
  }

  return props.amount === undefined ? [] : [{ amount: props.amount, currency: props.currency, decimals: props.decimals }]
})
</script>

<template>
  <button
    v-if="variant === 'new'"
    type="button"
    class="flex min-w-0 flex-col items-center justify-center gap-2.5 rounded-card border border-dashed border-line bg-transparent p-[14px] text-text-3 transition hover:border-primary hover:text-primary md:min-h-[172px] md:p-[18px]"
  >
    <span class="grid size-[38px] place-items-center rounded-full border border-current">
      <Plus class="size-5" :stroke-width="1.5" />
    </span>
    <span class="text-[13px] font-medium">{{ label }}</span>
  </button>

  <div
    v-else
    class="flex min-w-0 cursor-pointer flex-col gap-3 rounded-card border border-transparent bg-surface p-[14px] transition hover:bg-surface-2 md:gap-4 md:p-[18px]"
  >
    <div class="flex items-start justify-between">
      <CategoryGlyph :color="color" size="sm" :glyph="glyph" />
      <AvatarGroup v-if="members && members.length" :items="members" size="xs" />
    </div>
    <div>
      <div class="mb-[3px] text-[15.5px] font-semibold tracking-[-0.02em]">{{ name }}</div>
      <div class="text-[12px] text-text-3">{{ meta }}</div>
    </div>
    <div
      v-for="(line, index) in amountLines"
      :key="index"
      class="num text-[18px] font-semibold whitespace-nowrap md:text-[22px]"
      :class="spendingToneClass(line.amount)"
    >
      {{ line.currency }} {{ formatAmount(line) }}
    </div>
    <div v-if="amountCaption && amountLines.length" class="-mt-2 text-[12px] text-text-3 md:-mt-3">{{ amountCaption }}</div>
    <div v-if="status" class="mt-auto hidden md:block">
      <Badge :variant="statusTone === 'pos' ? 'ok' : 'pill'" class="whitespace-nowrap" :class="statusTone === 'neutral' ? 'text-text-2' : ''">
        {{ status }}
        <span v-if="statusExtra" class="text-text-3">· {{ statusExtra }}</span>
      </Badge>
    </div>
  </div>
</template>
