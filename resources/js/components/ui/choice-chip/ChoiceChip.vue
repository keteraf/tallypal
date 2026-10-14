<script setup lang="ts">
import type { CategoryColor } from "@/lib/category"
import { asTextGlyph } from "@/components/ui/category-glyph"
import { computed } from "vue"

const props = defineProps<{
  selected?: boolean
  /** Leading swatch dot from the categorical palette. */
  color?: CategoryColor
  /** Emoji / character shown as the chip content (alternative to the label / slot). */
  glyph?: string
  label?: string
}>()

const dotStyle = computed(() => ({
  background: props.selected ? "currentColor" : `var(--${props.color})`,
}))
</script>

<template>
  <button
    type="button"
    class="inline-flex items-center gap-[7px] rounded-full px-[14px] py-[9px] text-[13px] font-medium transition"
    :class="selected ? 'bg-primary text-primary-foreground' : 'bg-surface-2 text-text-2 hover:bg-surface-3'"
    :aria-pressed="selected"
  >
    <span v-if="color" class="inline-block size-[7px] shrink-0 rounded-full" :style="dotStyle" />
    <slot>{{ label ?? asTextGlyph(glyph) }}</slot>
  </button>
</template>
