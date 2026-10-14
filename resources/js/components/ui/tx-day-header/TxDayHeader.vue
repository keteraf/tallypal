<script setup lang="ts">
defineProps<{
  label: string
  /** Running total for the day (pre-formatted). */
  total?: string
}>()
</script>

<template>
  <div class="mt-5 mb-1 flex justify-between text-[12px] font-semibold text-text-3">
    <span>{{ label }}</span>
    <span v-if="total" class="num font-medium">{{ total }}</span>
  </div>
</template>
