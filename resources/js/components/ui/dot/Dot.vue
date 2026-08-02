<script setup lang="ts">
import type { CategoryColor } from "@/lib/category"
import { computed } from "vue"

type DotColor = CategoryColor | "accent" | "pos" | "neg" | "text-3"

const props = withDefaults(
  defineProps<{
    color?: DotColor
    /** Diameter in px (source `.dotc` = 7). */
    size?: number
  }>(),
  {
    color: "accent",
    size: 7,
  },
)

const style = computed(() => ({
  width: `${props.size}px`,
  height: `${props.size}px`,
  background: `var(--${props.color})`,
}))
</script>

<template>
  <span class="inline-block shrink-0 rounded-full" :style="style" />
</template>
