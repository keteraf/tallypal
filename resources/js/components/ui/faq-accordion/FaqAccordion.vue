<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { FaqItemData } from "."
import FaqItem from "./FaqItem.vue"
import { cn } from "@/lib/utils"

withDefaults(
  defineProps<{
    items: FaqItemData[]
    /** Index open by default (source: first item `open`). */
    defaultOpen?: number
    class?: HTMLAttributes["class"]
  }>(),
  {
    defaultOpen: 0,
  },
)
</script>

<template>
  <div :class="cn('grid grid-cols-[repeat(auto-fit,minmax(320px,1fr))] gap-x-7 gap-y-3', $props.class)">
    <FaqItem
      v-for="(item, index) in items"
      :key="item.question"
      :question="item.question"
      :answer="item.answer"
      :open="index === defaultOpen"
    />
  </div>
</template>
