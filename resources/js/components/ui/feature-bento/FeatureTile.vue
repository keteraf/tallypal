<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { CategoryColor } from "@/lib/category"
import { CategoryGlyph } from "@/components/ui/category-glyph"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<{
    title: string
    /** Emoji / character shown in the leading glyph square. */
    glyph: string
    color: CategoryColor
    /** Grid column span at `≥900px` (source `.tile.w2`/`.w3`/`.w6`); always full-width below that. */
    span?: 2 | 3 | 6
    class?: HTMLAttributes["class"]
  }>(),
  {
    span: 3,
  },
)

const spanClass: Record<2 | 3 | 6, string> = {
  2: "min-[900px]:col-span-2",
  3: "min-[900px]:col-span-3",
  6: "min-[900px]:col-span-6",
}
</script>

<template>
  <article
    :class="
      cn(
        'col-span-2 flex min-w-0 flex-col gap-4 rounded-lg border border-line-soft bg-surface p-[26px] transition duration-[250ms] hover:-translate-y-0.5 hover:border-[color-mix(in_oklch,var(--accent)_45%,transparent)]',
        spanClass[props.span],
        $props.class,
      )
    "
  >
    <div class="flex items-center gap-2.5">
      <CategoryGlyph :color="color" size="sm" :glyph="glyph" />
      <h3 class="m-0 text-tile-h font-semibold tracking-[-0.03em]">{{ title }}</h3>
    </div>
    <p class="m-0 max-w-[46ch] text-[14.5px] text-text-2">
      <slot />
    </p>
    <div v-if="$slots.demo" class="mt-auto rounded-card border border-line bg-surface-inset p-4">
      <slot name="demo" />
    </div>
  </article>
</template>
