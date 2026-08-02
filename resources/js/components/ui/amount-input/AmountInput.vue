<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { useVModel } from "@vueuse/core"
import { computed } from "vue"

/**
 * Big centred amount entry for the Add-transaction sheet (`.amt-in` in the design
 * source): a mono figure preceded by a small currency mark, with an optional caption
 * underneath. The figure is a real always-editable input rather than the source's
 * tap-to-reveal display, so it stays keyboard- and screen-reader-usable;
 * `inputmode="decimal"` still raises the numeric keypad on touch devices.
 */
const props = withDefaults(
  defineProps<{
    modelValue?: string | number
    defaultValue?: string | number
    /** Currency mark shown before the figure (symbol or ISO code). */
    currency?: string
    /** Fraction digits used to build the placeholder figure. */
    decimals?: number
    /** Accessible name for the field — rendered visually hidden. */
    label?: string
    /** Caption under the figure. */
    hint?: string
    id?: string
    class?: HTMLAttributes["class"]
  }>(),
  {
    decimals: 2,
  },
)

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void
}>()

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

const placeholder = computed(() => (props.decimals > 0 ? `0.${"0".repeat(props.decimals)}` : "0"))

/** Mono glyphs are one `ch` wide, so the field can hug its value and stay centred. */
const fieldWidth = computed(() => {
  const length = String(modelValue.value ?? "").length

  return `${Math.max(length, placeholder.value.length)}ch`
})
</script>

<template>
  <div :class="cn('pt-2.5 pb-5 text-center', props.class)">
    <div class="tabular flex items-baseline justify-center gap-[5px] font-mono text-[46px] leading-none font-semibold tracking-[-0.045em]">
      <!-- The `currency` slot swaps the static mark for a picker (F-092). -->
      <slot name="currency">
        <span v-if="currency" class="text-[26px] font-medium text-text-3">{{ currency }}</span>
      </slot>
      <label v-if="label" :for="id" class="sr-only">{{ label }}</label>
      <input
        :id="id"
        v-model="modelValue"
        type="text"
        inputmode="decimal"
        autocomplete="off"
        :placeholder="placeholder"
        :style="{ width: fieldWidth }"
        class="tabular min-w-0 appearance-none border-0 bg-transparent p-0 text-center font-mono text-[46px] leading-none font-semibold tracking-[-0.045em] text-text outline-none placeholder:text-text-3"
      >
    </div>
    <p v-if="hint" class="mt-2 text-[12px] text-text-3">{{ hint }}</p>
  </div>
</template>
