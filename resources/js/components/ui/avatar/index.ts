import type { CategoryColor } from "@/lib/category"

export { default as Avatar } from "./Avatar.vue"
export { default as AvatarFallback } from "./AvatarFallback.vue"
export { default as AvatarGroup } from "./AvatarGroup.vue"
export { default as AvatarImage } from "./AvatarImage.vue"

export interface AvatarGroupItem {
  initials?: string
  src?: string
  alt?: string
  color?: CategoryColor
}
