<script setup lang="ts">
import type { QuickAction } from "."

withDefaults(
  defineProps<{
    actions: QuickAction[]
    /** `card` renders the titled surface card with 2×2 buttons (Home); `row` the bare 4-up grid. */
    variant?: "row" | "card"
    title?: string
  }>(),
  { variant: "row" },
)
</script>

<template>
  <section v-if="variant === 'card'" class="rounded-card bg-surface p-4 sm:p-5">
    <h3 v-if="title" class="mb-3.5 text-[15px] leading-tight font-semibold tracking-[-0.02em] text-text">{{ title }}</h3>
    <div class="grid grid-cols-2 gap-2.5">
      <button
        v-for="(action, i) in actions"
        :key="i"
        type="button"
        class="flex flex-col items-start gap-2.5 rounded-(--radius) bg-surface-2 px-3 py-3.5 text-left text-text transition-colors duration-200 hover:bg-surface-3 sm:flex-row sm:items-center sm:gap-3 sm:px-3.5 sm:py-4"
        @click="action.onClick?.()"
      >
        <span
          class="grid size-[34px] shrink-0 place-items-center rounded-full"
          :class="action.primary ? 'bg-primary text-primary-foreground' : 'bg-surface-3 text-text'"
        >
          <component :is="action.icon" v-if="action.icon" class="size-4" :stroke-width="action.primary ? 2.4 : 1.9" />
        </span>
        <span class="min-w-0">
          <span class="block text-[13.5px] font-semibold tracking-[-0.01em]">{{ action.label }}</span>
          <span v-if="action.sub" class="mt-0.5 hidden text-[11.5px] text-text-3 sm:block">{{ action.sub }}</span>
        </span>
      </button>
    </div>
  </section>
  <div v-else class="mt-[22px] grid grid-cols-4 gap-2">
    <button
      v-for="(action, i) in actions"
      :key="i"
      type="button"
      class="flex flex-col items-center gap-[7px] rounded-[14px] bg-surface px-1 pt-[13px] pb-[11px] text-text transition hover:bg-surface-2"
      @click="action.onClick?.()"
    >
      <span
        class="grid size-[34px] place-items-center rounded-full"
        :class="action.primary ? 'bg-primary text-primary-foreground' : 'bg-surface-3 text-text'"
      >
        <component :is="action.icon" v-if="action.icon" class="size-4" :stroke-width="action.primary ? 2.4 : 1.9" />
      </span>
      <span class="text-[11px] font-medium tracking-[-0.01em]">{{ action.label }}</span>
    </button>
  </div>
</template>
