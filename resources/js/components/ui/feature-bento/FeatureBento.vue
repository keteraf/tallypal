<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div :class="cn('grid grid-cols-2 gap-3.5 min-[900px]:grid-cols-6', $props.class)">
    <slot />
  </div>
</template>
