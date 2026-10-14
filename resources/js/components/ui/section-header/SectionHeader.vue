<script setup lang="ts">
import { Link } from "@inertiajs/vue3"

defineProps<{
  label: string
  actionLabel?: string
  actionHref?: string
}>()
</script>

<template>
  <div class="mt-[26px] mb-3 flex items-center justify-between">
    <span class="text-[12px] font-semibold uppercase tracking-[0.06em] text-text-3">{{ label }}</span>
    <slot name="action">
      <Link
        v-if="actionHref"
        :href="actionHref"
        class="text-[12px] font-medium text-primary transition hover:text-text"
      >
        {{ actionLabel }}
      </Link>
      <button
        v-else-if="actionLabel"
        type="button"
        class="text-[12px] font-medium text-primary transition hover:text-text"
      >
        {{ actionLabel }}
      </button>
    </slot>
  </div>
</template>
