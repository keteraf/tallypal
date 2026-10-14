<script setup lang="ts">
withDefaults(
  defineProps<{
    items: { value: string; label: string }[]
  }>(),
  {},
)
</script>

<template>
  <div class="mt-[38px] flex flex-wrap gap-[26px] border-t border-line pt-[26px]">
    <div v-for="item in items" :key="item.label">
      <div class="num text-proof-k font-semibold">{{ item.value }}</div>
      <div class="mt-0.5 text-xs text-text-3">{{ item.label }}</div>
    </div>
  </div>
</template>
