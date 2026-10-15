<script setup lang="ts">
import { CategoryGlyph } from "@/components/ui/category-glyph"
import type { CategoryColor } from "@/lib/category"
import { cn } from "@/lib/utils"
import { computed } from "vue"

/**
 * One tile of the category picker (`.ct` in `Tallypal Categories.html`, DESIGN_TOKENS §7.2): a 30px
 * tinted glyph square over a two-line label. `variant="all"` is the "All N" tile (⋯), `open` while its
 * panel or sheet shows. `preview` draws the hover / keyboard-focus look statically, for the design-system
 * page only; real tiles get both from `:hover` and `:focus-visible`.
 */
const props = withDefaults(
  defineProps<{
    glyph: string
    label: string
    color?: CategoryColor
    selected?: boolean
    variant?: "category" | "all"
    open?: boolean
    /** Phone sheet grid: 80px instead of 76px. */
    tall?: boolean
    preview?: "hover" | "focus"
  }>(),
  {
    color: undefined,
    variant: "category",
    preview: undefined,
  },
)

const surface = computed(() => {
  if (props.selected) {
    return "bg-[color-mix(in_oklch,var(--accent)_16%,var(--surface-2))] text-text shadow-[inset_0_0_0_1.5px_var(--accent)]"
  }

  if (props.open || props.preview === "hover") {
    return "bg-surface-3 text-text"
  }

  return "bg-surface-2 text-text-2 hover:bg-surface-3 hover:text-text"
})

const square = computed(() => {
  if (props.variant === "all") {
    return props.open ? "bg-text text-surface tracking-[0.06em]" : "bg-surface-3 text-text-2 tracking-[0.06em]"
  }

  return props.selected ? "bg-accent! text-accent-ink!" : ""
})
</script>

<template>
  <button
    type="button"
    :class="
      cn(
        'flex min-h-[76px] min-w-0 cursor-pointer flex-col items-center gap-1.5 rounded-[var(--radius)] px-1 pt-[9px] pb-2 font-sans transition-[background-color,box-shadow] duration-150 outline-none focus-visible:outline-solid focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
        tall && 'min-h-20',
        preview === 'focus' && 'outline-solid outline-2 outline-offset-2 outline-accent',
        surface,
      )
    "
  >
    <CategoryGlyph size="tile" :color="variant === 'all' ? undefined : color" :glyph="glyph" :class="['transition-colors duration-150', square]" />
    <span class="line-clamp-2 max-w-full text-center text-[10.5px] leading-[1.15] font-medium tracking-[-0.015em]">{{ label }}</span>
  </button>
</template>
