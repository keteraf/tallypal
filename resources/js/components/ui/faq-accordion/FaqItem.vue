<script setup lang="ts">
withDefaults(
  defineProps<{
    question: string
    answer: string
    open?: boolean
  }>(),
  {
    open: false,
  },
)
</script>

<template>
  <details class="group h-fit rounded-sm border border-line-soft bg-surface px-[18px] py-4" :open="open">
    <summary
      class="flex cursor-pointer list-none items-center justify-between gap-4 text-[15px] font-medium tracking-[-0.02em] [&::-webkit-details-marker]:hidden"
    >
      {{ question }}
      <span class="font-mono text-[17px] text-text-3">
        <span class="group-open:hidden">+</span>
        <span class="hidden group-open:inline">–</span>
      </span>
    </summary>
    <p class="m-0 mt-3 mb-0.5 text-[14.5px] text-text-2 text-pretty">{{ answer }}</p>
  </details>
</template>
