<script setup lang="ts">
withDefaults(
  defineProps<{
    number: string
    title: string
  }>(),
  {},
)
</script>

<template>
  <div class="flex flex-col gap-3 rounded-card border border-line-soft bg-surface px-[22px] pt-[22px] pb-[24px]">
    <span class="grid size-[30px] place-items-center rounded-[10px] bg-surface-2 font-mono text-xs font-semibold text-text-2">{{ number }}</span>
    <h3 class="m-0 text-[18px] font-semibold tracking-[-0.025em]">{{ title }}</h3>
    <p class="m-0 text-[14.5px] text-text-2">
      <slot />
    </p>
  </div>
</template>
