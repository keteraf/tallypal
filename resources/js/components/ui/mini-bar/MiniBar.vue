<script setup lang="ts">
import type { CategoryColor } from "@/lib/category"
import { computed } from "vue"

const props = defineProps<{
  /** Filled percentage, 0–100. */
  value: number
  /** `surface-3` is the muted fill of the "Other" row. */
  color: CategoryColor | "surface-3"
}>()

const fillStyle = computed(() => ({
  width: `${Math.max(0, Math.min(100, props.value))}%`,
  background: `var(--${props.color})`,
}))
</script>

<template>
  <div class="h-[5px] w-full overflow-hidden rounded-full bg-surface-2">
    <div class="h-full rounded-full" :style="fillStyle" />
  </div>
</template>
