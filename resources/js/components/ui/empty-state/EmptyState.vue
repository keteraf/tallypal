<script setup lang="ts">
import { Button } from "@/components/ui/button"
import { Link } from "@inertiajs/vue3"

defineProps<{
  title: string
  description?: string
  /** Renders a primary CTA when set together with `actionHref` or via `@action`. */
  actionLabel?: string
  actionHref?: string
}>()

defineEmits<{
  action: []
}>()
</script>

<template>
  <div class="flex flex-col items-center gap-1.5 rounded-card bg-surface p-4 text-center sm:p-5">
    <h3 class="m-0 text-[15px] leading-tight font-semibold tracking-[-0.02em] text-text">{{ title }}</h3>
    <p v-if="description" class="m-0 max-w-[44ch] text-[13px] text-text-3">{{ description }}</p>
    <div v-if="$slots.action || actionLabel" class="mt-3.5">
      <slot name="action">
        <Button v-if="actionHref" size="touch" as-child>
          <Link :href="actionHref">{{ actionLabel }}</Link>
        </Button>
        <Button v-else size="touch" type="button" @click="$emit('action')">{{ actionLabel }}</Button>
      </slot>
    </div>
  </div>
</template>
