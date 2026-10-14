<script setup lang="ts">
withDefaults(
  defineProps<{
    /** Bleed to the screen edges (`-mx-5`) as on the mobile Home screen. */
    bleed?: boolean
  }>(),
  {
    bleed: false,
  },
)
</script>

<template>
  <div
    class="flex snap-x snap-mandatory gap-[10px] overflow-x-auto pt-0.5 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
    :class="bleed ? '-mx-5 px-5' : ''"
  >
    <slot />
  </div>
</template>
