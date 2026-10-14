<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ChevronRight } from "lucide-vue-next"

withDefaults(
  defineProps<{
    title: string
    description?: string
    /** `neg` colours the control text, for destructive rows (Sign out, Delete account). */
    tone?: "default" | "neg"
    /** Makes the whole row a link and adds a trailing chevron. */
    href?: string
  }>(),
  {
    tone: "default",
  },
)
</script>

<template>
  <component
    :is="href ? Link : 'div'"
    :href="href"
    :class="[
      'flex items-center gap-3 border-t border-line py-[13px] first:border-t-0 first:pt-1',
      href ? 'transition hover:opacity-80' : '',
      tone === 'neg' ? 'text-neg' : '',
    ]"
  >
    <div class="min-w-0 flex-1">
      <div :class="['text-[13.5px] font-medium tracking-[-0.01em]', tone === 'neg' ? 'text-neg' : 'text-text']">{{ title }}</div>
      <div v-if="description" class="mt-0.5 text-[11.5px] text-text-3">{{ description }}</div>
    </div>
    <div v-if="$slots.control || href" :class="['shrink-0', tone === 'neg' ? 'text-neg' : '']">
      <slot name="control">
        <ChevronRight class="size-4 text-text-3" aria-hidden="true" />
      </slot>
    </div>
  </component>
</template>
