<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useVModel } from "@vueuse/core"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
  defaultValue?: string | number
  modelValue?: string | number
}>()

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void
}>()

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
})
</script>

<template>
  <textarea
    v-model="modelValue"
    :class="cn(
      'flex min-h-24 w-full rounded-sm border border-rule bg-transparent px-3.5 py-3 text-sm text-foreground placeholder:text-muted-foreground transition-[color,border-color] outline-none focus-visible:border-brand focus-visible:ring-0 disabled:cursor-not-allowed disabled:opacity-50 font-sans leading-relaxed',
      props.class,
    )"
  />
</template>
