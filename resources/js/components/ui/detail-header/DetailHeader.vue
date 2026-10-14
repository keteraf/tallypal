<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ChevronLeft } from "lucide-vue-next"

defineProps<{
  title: string
  subtitle?: string
  /** When set, the back control navigates here; otherwise it emits `back`. */
  backHref?: string
}>()

defineEmits<{
  (e: "back"): void
}>()

const backClass =
  "grid size-9 shrink-0 place-items-center rounded-full bg-surface text-text transition hover:bg-surface-2"
</script>

<template>
  <div class="flex flex-wrap items-center gap-3 pt-2 pb-[18px]">
    <Link v-if="backHref" :href="backHref" :class="backClass" aria-label="Back">
      <ChevronLeft class="size-[17px]" :stroke-width="2.2" />
    </Link>
    <button v-else type="button" :class="backClass" aria-label="Back" @click="$emit('back')">
      <ChevronLeft class="size-[17px]" :stroke-width="2.2" />
    </button>
    <slot name="glyph" />
    <span class="min-w-0 flex-1">
      <div class="truncate text-[18px] font-semibold tracking-[-0.025em]">{{ title }}</div>
      <div v-if="subtitle" class="mt-0.5 text-[12px] text-text-3">{{ subtitle }}</div>
    </span>
    <div v-if="$slots.actions" class="flex w-full items-center justify-end gap-2.5">
      <slot name="actions" />
    </div>
  </div>
</template>
