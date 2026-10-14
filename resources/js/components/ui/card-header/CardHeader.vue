<script setup lang="ts">
import { Link } from "@inertiajs/vue3"

withDefaults(
  defineProps<{
    title: string
    /** Heading element rendered for the title. */
    as?: string
    actionLabel?: string
    actionHref?: string
  }>(),
  {
    as: "h3",
  },
)

const actionClass = "text-[12.5px] font-medium text-accent transition hover:text-text"
</script>

<template>
  <div class="mb-3.5 flex items-center justify-between gap-3">
    <component :is="as" class="m-0 text-[15px] leading-tight font-semibold tracking-[-0.02em] text-text">{{ title }}</component>
    <div v-if="$slots.action || $slots.meta || actionLabel" class="flex shrink-0 items-center gap-3">
      <slot name="meta" />
      <slot name="action">
        <Link v-if="actionHref && actionLabel" :href="actionHref" :class="actionClass">{{ actionLabel }}</Link>
        <button v-else-if="actionLabel" type="button" :class="actionClass">{{ actionLabel }}</button>
      </slot>
    </div>
  </div>
</template>
