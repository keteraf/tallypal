<script setup lang="ts">
import { computed } from "vue"
import InputError from "@/components/InputError.vue"
import { provideFormField } from "./context"

const props = defineProps<{
  label?: string
  /** `id` of the control this label points at; the error message id derives from it. */
  for?: string
  error?: string
}>()

const errorId = computed(() => (props.for ? `${props.for}-error` : undefined))

provideFormField({
  invalid: computed(() => !!props.error),
  describedBy: errorId,
})
</script>

<template>
  <div class="mb-[14px]">
    <label
      v-if="label"
      :for="$props.for"
      class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.06em] text-text-3"
    >
      {{ label }}
    </label>
    <slot />
    <InputError :id="errorId" :message="error" class="mt-1.5 [&_p]:text-[12px]" />
  </div>
</template>
