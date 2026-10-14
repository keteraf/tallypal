<script setup lang="ts">
import { Lock } from "lucide-vue-next"

withDefaults(
  defineProps<{
    label?: string
  }>(),
  {
    label: "Pin this share",
  },
)

const locked = defineModel<boolean>("locked", { default: false })
</script>

<template>
  <button
    type="button"
    class="grid size-7 shrink-0 place-items-center rounded-full transition hover:bg-surface-3 hover:text-text"
    :class="locked ? 'text-accent' : 'text-text-3'"
    :aria-pressed="locked"
    :aria-label="label"
    :title="label"
    @click="locked = !locked"
  >
    <Lock class="size-[13px]" />
  </button>
</template>
