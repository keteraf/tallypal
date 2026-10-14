<script setup lang="ts">
import { computed } from "vue"
import { useVModel } from "@vueuse/core"
import { Minus, Plus } from "lucide-vue-next"

const props = withDefaults(
  defineProps<{
    modelValue?: number
    min?: number
    max?: number
    step?: number
    /** Suffix rendered after the value, e.g. `%`. */
    suffix?: string
    /** `sm` = the split editor's `.pct`: surface pill with an editable mono input. */
    size?: "default" | "sm"
    /** Accessible name of the editable input (`sm` only). */
    label?: string
  }>(),
  {
    modelValue: 0,
    min: 0,
    max: 100,
    step: 5,
    suffix: "",
    size: "default",
    label: "Value",
  },
)

const emits = defineEmits<{
  (e: "update:modelValue", value: number): void
}>()

const value = useVModel(props, "modelValue", emits, { passive: true })

const current = computed(() => value.value ?? 0)

const clamp = (v: number): number => Math.max(props.min, Math.min(props.max, v))

const decrement = () => {
  value.value = clamp(current.value - props.step)
}
const increment = () => {
  value.value = clamp(current.value + props.step)
}

const commitInput = (event: Event) => {
  const input = event.target as HTMLInputElement
  const parsed = Number.parseInt(input.value, 10)
  value.value = clamp(Number.isNaN(parsed) ? current.value : parsed)
  input.value = String(value.value)
}

const isSmall = computed(() => props.size === "sm")
</script>

<template>
  <div
    class="inline-flex items-center"
    :class="isSmall ? 'shrink-0 rounded-full bg-surface p-0.5 transition' : 'gap-0.5 rounded-full bg-surface-2 p-[3px]'"
  >
    <button
      type="button"
      class="grid size-[26px] place-items-center rounded-full text-text-2 transition hover:bg-surface-3 hover:text-text disabled:pointer-events-none disabled:opacity-40"
      :disabled="current <= min"
      :aria-label="isSmall ? 'Less' : 'Decrease'"
      @click="decrement"
    >
      <Minus class="size-4" />
    </button>
    <template v-if="isSmall">
      <input
        :value="current"
        type="text"
        inputmode="numeric"
        :aria-label="label"
        class="num w-[34px] border-0 bg-transparent p-0 text-right text-[13px] font-semibold text-text outline-none"
        @change="commitInput"
        @keydown.enter.prevent="commitInput"
      />
      <span v-if="suffix" class="mr-1 ml-px font-mono text-xs text-text-3">{{ suffix }}</span>
    </template>
    <span v-else class="num min-w-[38px] text-center text-[13px] font-semibold">{{ current }}{{ suffix }}</span>
    <button
      type="button"
      class="grid size-[26px] place-items-center rounded-full text-text-2 transition hover:bg-surface-3 hover:text-text disabled:pointer-events-none disabled:opacity-40"
      :disabled="current >= max"
      :aria-label="isSmall ? 'More' : 'Increase'"
      @click="increment"
    >
      <Plus class="size-4" />
    </button>
  </div>
</template>
