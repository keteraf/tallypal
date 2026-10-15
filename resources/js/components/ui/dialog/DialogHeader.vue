<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    data-slot="dialog-header"
    :class="cn('flex flex-col gap-3 pt-6 pr-18 pb-5 pl-7 border-b border-line', props.class)"
  >
    <slot />
  </div>
</template>
