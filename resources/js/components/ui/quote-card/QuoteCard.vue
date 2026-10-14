<script setup lang="ts">
import type { CategoryColor } from "@/lib/category"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"

withDefaults(
  defineProps<{
    quote: string
    author: string
    context: string
    initials: string
    color: CategoryColor
  }>(),
  {},
)
</script>

<template>
  <div class="flex h-full flex-col gap-4 rounded-card border border-line-soft bg-surface p-6">
    <p class="m-0 text-quote tracking-[-0.015em] text-text-2">{{ quote }}</p>
    <div class="mt-auto flex items-center gap-2.5">
      <Avatar size="sm">
        <AvatarFallback :color="color">{{ initials }}</AvatarFallback>
      </Avatar>
      <span>
        <div class="text-[13.5px] font-semibold">{{ author }}</div>
        <div class="text-xs text-text-3">{{ context }}</div>
      </span>
    </div>
  </div>
</template>
