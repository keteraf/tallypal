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
    :class="cn('flex flex-col gap-3 px-7 pt-6 pb-5 border-b border-line', props.class)"
  >
    <slot />
  </div>
</template>
