<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { useVModel } from '@vueuse/core'
import { useFormFieldAria } from '@/components/ui/form-field/context'
import { filledFieldClass } from './field'

const props = withDefaults(
  defineProps<{
    defaultValue?: string | number
    modelValue?: string | number
    class?: HTMLAttributes['class']
    /** `default` = editorial underline; `filled` = mobile surface field. */
    variant?: 'default' | 'filled'
  }>(),
  {
    variant: 'default',
  },
)

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

const { ariaInvalid, ariaDescribedby } = useFormFieldAria()

const variantClass =
  props.variant === 'filled'
    ? cn(
        filledFieldClass,
        'appearance-none placeholder:text-text-3 focus-visible:border-accent aria-invalid:border-neg aria-invalid:focus-visible:border-neg disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 font-sans',
      )
    : 'placeholder:text-muted-foreground selection:bg-accent selection:text-accent-ink flex h-11 w-full min-w-0 rounded-none border-0 border-b border-line bg-transparent px-0 py-2.5 text-base text-foreground transition-[color,border-color] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-base font-sans focus-visible:border-accent focus-visible:ring-0 aria-invalid:border-destructive'
</script>

<template>
  <input
    v-model="modelValue"
    data-slot="input"
    :aria-invalid="ariaInvalid"
    :aria-describedby="ariaDescribedby"
    :class="cn(variantClass, props.class)"
  >
</template>
