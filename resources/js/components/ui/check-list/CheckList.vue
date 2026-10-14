<script setup lang="ts">
import type { CheckListItemData } from "."
import CheckListItem from "./CheckListItem.vue"

withDefaults(
  defineProps<{
    items: CheckListItemData[]
    /** Color used for `on` ticks across the whole list. */
    tone?: "neutral" | "pos" | "accent"
  }>(),
  {
    tone: "neutral",
  },
)
</script>

<template>
  <ul class="flex list-none flex-col gap-[9px] p-0">
    <CheckListItem
      v-for="item in items"
      :key="item.label"
      :label="item.label"
      :state="item.state"
      :tone="tone"
    />
  </ul>
</template>
