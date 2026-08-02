<script setup lang="ts">
import { Badge } from "@/components/ui/badge"
import { Dot } from "@/components/ui/dot"

withDefaults(
  defineProps<{
    /** Halo + dot color (source `.badge .d` — always `--pos` in the mock). */
    dotColor?: "pos" | "accent" | "neg"
  }>(),
  {
    dotColor: "pos",
  },
)
</script>

<template>
  <Badge variant="status">
    <span
      class="inline-flex shrink-0 rounded-full"
      :style="{ boxShadow: `0 0 0 4px color-mix(in oklch, var(--${dotColor}) 22%, transparent)` }"
    >
      <Dot :color="dotColor" :size="6" />
    </span>
    <slot />
  </Badge>
</template>
