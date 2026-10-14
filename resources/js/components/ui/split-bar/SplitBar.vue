<script setup lang="ts">
import type { CategoryColor } from "@/lib/category"

defineProps<{
  segments: { value: number; color: CategoryColor }[]
}>()
</script>

<template>
  <div class="mb-1 flex h-2 gap-0.5 overflow-hidden rounded-full bg-surface-3" role="presentation">
    <i
      v-for="(segment, index) in segments"
      :key="index"
      class="block h-full transition-[width] duration-250"
      :style="{ width: `${Math.max(0, segment.value)}%`, background: `var(--${segment.color})` }"
    />
  </div>
</template>
