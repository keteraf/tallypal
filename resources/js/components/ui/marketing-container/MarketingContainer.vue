<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

withDefaults(
  defineProps<{
    /** Element/component to render as (source `.wrap`, e.g. on a `<section>`). */
    as?: string
    class?: HTMLAttributes["class"]
  }>(),
  {
    as: "div",
  },
)
</script>

<template>
  <component :is="as" :class="cn('mx-auto max-w-site px-7', $props.class)">
    <slot />
  </component>
</template>
