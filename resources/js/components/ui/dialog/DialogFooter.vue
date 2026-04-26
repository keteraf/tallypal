<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{ class?: HTMLAttributes['class'] }>()
</script>

<template>
  <div
    data-slot="dialog-footer"
    :class="cn('flex flex-col-reverse gap-3 sm:flex-row sm:justify-end px-7 py-4 border-t border-rule bg-paper-2', props.class)"
  >
    <slot />
  </div>
</template>
