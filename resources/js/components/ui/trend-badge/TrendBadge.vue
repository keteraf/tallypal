<script setup lang="ts">
import { computed } from "vue"
import { ArrowDown, ArrowUp } from "lucide-vue-next"

const props = withDefaults(
  defineProps<{
    /**
     * `pos-neg`: a tinted pill, up is `--pos`, down is `--neg`. Only where the direction is
     * unambiguously good or bad for the user (the Home balance delta). `neutral`: no pill, plain
     * `--text-3` sub-line text (arrow, value and caption) for everything else, e.g. spending.
     */
    tone: "pos-neg" | "neutral"
    direction?: "up" | "down"
    /** Formatted value, e.g. `12.4%`. */
    value: string | number
    /** Trailing caption, e.g. `vs March`. */
    label?: string
  }>(),
  {
    direction: "up",
  },
)

const isUp = computed(() => props.direction === "up")

const style = computed(() =>
  props.tone === "pos-neg" ? { background: `color-mix(in oklch, var(--${isUp.value ? "pos" : "neg"}) 16%, transparent)` } : undefined,
)

const toneClass = computed(() => {
  if (props.tone === "neutral") {
    return "text-[12px] text-text-3"
  }

  return `rounded-full px-[11px] py-[5px] text-[13px] font-medium ${isUp.value ? "text-pos" : "text-neg"}`
})
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5"
    :class="toneClass"
    :style="style"
  >
    <component :is="isUp ? ArrowUp : ArrowDown" class="size-[11px]" :stroke-width="3" />
    <span>{{ value }}</span>
    <span v-if="label">{{ label }}</span>
  </span>
</template>
