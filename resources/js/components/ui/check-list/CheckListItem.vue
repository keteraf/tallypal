<script setup lang="ts">
import { Check } from "lucide-vue-next"

withDefaults(
  defineProps<{
    label: string
    /** @default "on" */
    state?: "on" | "off"
    /** Color used for the `on` tick — `pos` (privacy tile) or `accent` (highlighted plan). */
    tone?: "neutral" | "pos" | "accent"
  }>(),
  {
    state: "on",
    tone: "neutral",
  },
)
</script>

<template>
  <li class="flex items-start gap-2.5 text-sm" :class="state === 'off' ? 'text-text-3' : 'text-text-2'">
    <span
      class="mt-0.5 grid size-[18px] shrink-0 place-items-center rounded-full"
      :class="{
        'border border-line bg-transparent text-text-3': state === 'off',
        'bg-surface-2 text-text-2': state === 'on' && tone === 'neutral',
        'bg-[color-mix(in_oklch,var(--pos)_20%,transparent)] text-pos': state === 'on' && tone === 'pos',
        'bg-[color-mix(in_oklch,var(--accent)_20%,transparent)] text-accent': state === 'on' && tone === 'accent',
      }"
    >
      <Check v-if="state === 'on'" class="size-2.5" :stroke-width="3.4" />
      <span v-else aria-hidden="true">—</span>
    </span>
    {{ label }}
  </li>
</template>
