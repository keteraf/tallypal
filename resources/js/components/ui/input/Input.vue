<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { useVModel } from '@vueuse/core'

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})
</script>

<template>
  <input
    v-model="modelValue"
    data-slot="input"
    :class="cn(
      'placeholder:text-muted-foreground selection:bg-brand selection:text-paper flex h-11 w-full min-w-0 rounded-none border-0 border-b border-rule bg-transparent px-0 py-2.5 text-base text-foreground transition-[color,border-color] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-base font-sans',
      'focus-visible:border-brand focus-visible:ring-0',
      'aria-invalid:border-destructive',
      props.class,
    )"
  >
</template>
