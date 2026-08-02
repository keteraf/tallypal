<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div
    data-slot="card-footer"
    :class="cn('flex items-center px-6 py-4 border-t border-line bg-surface-2', props.class)"
  >
    <slot />
  </div>
</template>
