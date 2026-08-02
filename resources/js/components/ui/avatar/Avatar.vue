<script setup lang="ts">
import type { VariantProps } from "class-variance-authority"
import type { HTMLAttributes } from "vue"
import { cva } from "class-variance-authority"
import { AvatarRoot } from "reka-ui"
import { cn } from "@/lib/utils"

const avatarVariants = cva(
  "relative flex shrink-0 items-center justify-center overflow-hidden rounded-full",
  {
    variants: {
      size: {
        xs: "size-[22px] text-[9px]",
        sm: "size-[26px] text-[10px]",
        md: "size-8",
        lg: "size-[38px] text-[13px]",
        xl: "size-14 text-lg",
      },
    },
    defaultVariants: {
      size: "md",
    },
  },
)

type AvatarVariants = VariantProps<typeof avatarVariants>

const props = defineProps<{
  class?: HTMLAttributes["class"]
  size?: AvatarVariants["size"]
}>()
</script>

<template>
  <AvatarRoot
    data-slot="avatar"
    :class="cn(avatarVariants({ size }), props.class)"
  >
    <slot />
  </AvatarRoot>
</template>
