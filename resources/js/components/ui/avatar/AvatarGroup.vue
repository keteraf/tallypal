<script setup lang="ts">
import type { AvatarGroupItem } from "."
import { computed } from "vue"
import { Avatar, AvatarFallback, AvatarImage } from "."

const props = withDefaults(
  defineProps<{
    items: AvatarGroupItem[]
    /** Cap the number of avatars shown; the rest collapse into a "+N" chip. */
    max?: number
    size?: "xs" | "sm" | "md" | "lg"
  }>(),
  {
    size: "xs",
  },
)

const visible = computed(() => (props.max ? props.items.slice(0, props.max) : props.items))
const overflow = computed(() => (props.max ? Math.max(0, props.items.length - props.max) : 0))
</script>

<template>
  <div class="flex items-center">
    <Avatar
      v-for="(item, i) in visible"
      :key="i"
      :size="size"
      class="ring-2 ring-surface"
      :class="i === 0 ? 'ml-0' : '-ml-[7px]'"
    >
      <AvatarImage v-if="item.src" :src="item.src" :alt="item.alt ?? item.initials ?? ''" />
      <AvatarFallback :color="item.color">{{ item.initials }}</AvatarFallback>
    </Avatar>
    <Avatar v-if="overflow > 0" :size="size" class="-ml-[7px] ring-2 ring-surface">
      <AvatarFallback>+{{ overflow }}</AvatarFallback>
    </Avatar>
  </div>
</template>
