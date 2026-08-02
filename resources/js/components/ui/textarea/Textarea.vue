<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useVModel } from "@vueuse/core"
import { useFormFieldAria } from "@/components/ui/form-field/context"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<{
    class?: HTMLAttributes["class"]
    defaultValue?: string | number
    modelValue?: string | number
    /** `default` = editorial outline; `filled` = mobile surface field. */
    variant?: "default" | "filled"
  }>(),
  {
    variant: "default",
  },
)

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void
}>()

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

const { ariaInvalid, ariaDescribedby } = useFormFieldAria()

const variantClass =
  props.variant === "filled"
    ? "flex min-h-24 w-full rounded-[14px] border border-transparent bg-surface-2 px-[15px] py-[14px] text-[15px] text-text outline-none transition placeholder:text-text-3 focus-visible:border-accent aria-invalid:border-neg aria-invalid:focus-visible:border-neg disabled:cursor-not-allowed disabled:opacity-50 font-sans leading-relaxed"
    : "flex min-h-24 w-full rounded-sm border border-line bg-transparent px-3.5 py-3 text-sm text-foreground placeholder:text-muted-foreground transition-[color,border-color] outline-none focus-visible:border-accent focus-visible:ring-0 disabled:cursor-not-allowed disabled:opacity-50 font-sans leading-relaxed"
</script>

<template>
  <textarea
    v-model="modelValue"
    :aria-invalid="ariaInvalid"
    :aria-describedby="ariaDescribedby"
    :class="cn(variantClass, props.class)"
  />
</template>
