<script setup lang="ts">
import type { GlyphColor } from "@/lib/category"
import { computed } from "vue"
import { asTextGlyph } from "./categoryGlyphs"

type CategoryGlyphSize = "xs" | "sm" | "md" | "lg" | "table" | "header" | "xl"

const props = withDefaults(
  defineProps<{
    color: GlyphColor
    /** `xs` = sidebar tally row (22, r7, 11px), `sm` = rail glyph (34), `md` = tx icon (42), `lg` = list glyph (44), `table` = DataTable cell (40), `header` = mobile detail header (40, r13, 17px), `xl` = desktop detail header (48, r16, 20px). */
    size?: CategoryGlyphSize
    /** Soft-fill opacity percentage; defaults per size (glyph 22, tx 20). */
    fill?: number
    /** Emoji / character glyph (alternative to the default slot). */
    glyph?: string
  }>(),
  {
    size: "md",
  },
)

const dims: Record<CategoryGlyphSize, string> = {
  xs: "h-[22px] w-[22px] rounded-[7px] text-[11px]",
  sm: "h-[34px] w-[34px] rounded-[11px] text-[15px]",
  md: "h-[42px] w-[42px] rounded-[14px] text-[15px]",
  lg: "h-[44px] w-[44px] rounded-[15px] text-[18px]",
  table: "h-10 w-10 rounded-[13px] text-[15px]",
  header: "h-10 w-10 rounded-[13px] text-[17px]",
  xl: "h-12 w-12 rounded-[16px] text-[20px]",
}

const defaultFill: Record<CategoryGlyphSize, number> = { xs: 22, sm: 22, md: 20, lg: 22, table: 20, header: 22, xl: 22 }

const style = computed(() => ({
  background: `color-mix(in oklch, var(--${props.color}) ${props.fill ?? defaultFill[props.size]}%, transparent)`,
  color: `var(--${props.color})`,
}))
</script>

<template>
  <span :class="['grid shrink-0 place-items-center font-semibold', dims[size]]" :style="style">
    <slot>{{ asTextGlyph(glyph) }}</slot>
  </span>
</template>
