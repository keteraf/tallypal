<script setup lang="ts">
withDefaults(
  defineProps<{
    heading: string
    body: string
  }>(),
  {},
)
</script>

<template>
  <div class="relative overflow-hidden rounded-lg border border-line bg-surface p-band text-center">
    <div
      aria-hidden="true"
      class="pointer-events-none absolute inset-x-0 -bottom-[70%] h-[240px]"
      style="background-image: var(--glow-band)"
    />

    <h2 class="relative mb-3.5 text-section font-semibold tracking-[-0.04em] text-balance">{{ heading }}</h2>
    <p class="relative mx-auto mb-[26px] max-w-[48ch] text-text-2">{{ body }}</p>
    <div class="relative flex flex-wrap items-center justify-center gap-3">
      <slot name="actions" />
    </div>
  </div>
</template>
