<script setup lang="ts">
import type { GlyphColor } from "@/lib/category"
import { CategoryGlyph } from "@/components/ui/category-glyph"

defineProps<{
  title: string
  /** Meta line under the title. */
  subtitle?: string
  color: GlyphColor
  glyph?: string
}>()
</script>

<template>
  <span class="flex min-w-0 items-center gap-[13px]">
    <CategoryGlyph :color="color" size="table" :glyph="glyph" />
    <span class="min-w-0">
      <span class="block truncate text-[14px] font-medium tracking-[-0.015em] text-text">{{ title }}</span>
      <span class="mt-0.5 flex flex-wrap items-center gap-y-1 text-[12px] text-text-3">
        <span v-if="subtitle">{{ subtitle }}</span>
        <!-- Phone only: the secondary cell is hidden, so the tag moves inline into the sub. -->
        <span class="min-[641px]:hidden"><slot name="inline" /></span>
      </span>
    </span>
  </span>
</template>
