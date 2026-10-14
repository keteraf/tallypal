<script setup lang="ts" generic="T extends string | number">
import type { ChipOption } from "."
import type { Ref } from "vue"
import { useVModel } from "@vueuse/core"
import ChoiceChip from "./ChoiceChip.vue"

const props = defineProps<{
  modelValue?: T | T[]
  options: ChipOption<T>[]
  /** Allow multiple selections (model becomes an array). */
  multiple?: boolean
}>()

const emits = defineEmits<{
  (e: "update:modelValue", value: T | T[]): void
}>()

const model = useVModel(props, "modelValue", emits, { passive: true }) as Ref<T | T[] | undefined>

const isSelected = (value: T): boolean => {
  if (props.multiple) {
    return Array.isArray(model.value) && model.value.includes(value)
  }

  return model.value === value
}

const toggle = (value: T) => {
  if (props.multiple) {
    const next = Array.isArray(model.value) ? [...model.value] : []
    const index = next.indexOf(value)

    if (index >= 0) {
      next.splice(index, 1)
    } else {
      next.push(value)
    }

    model.value = next
    return
  }

  model.value = value
}
</script>

<template>
  <div class="flex flex-wrap gap-[7px]">
    <ChoiceChip
      v-for="option in options"
      :key="String(option.value)"
      :selected="isSelected(option.value)"
      :color="option.color"
      :glyph="option.glyph"
      :label="option.label"
      @click="toggle(option.value)"
    />
  </div>
</template>
