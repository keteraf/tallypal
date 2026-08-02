<script setup lang="ts">
withDefaults(
  defineProps<{
    /** Light-theme screenshot of the real `/home` screen (source: no scaled Vue replica, #M6). */
    src: string
    /** Dark-theme counterpart. */
    srcDark: string
    /** Status-bar right-hand label. */
    label?: string
  }>(),
  {
    label: "Home",
  },
)
</script>

<template>
  <div class="relative flex min-h-[520px] items-center justify-center">
    <div class="relative w-[min(300px,86vw)] shrink-0 min-[520px]:w-[330px]">
      <slot />

      <div class="relative z-[2] rounded-device bg-bg p-[10px] shadow-device">
        <div class="overflow-hidden rounded-device-inner border border-line">
          <div class="flex items-center justify-between px-[18px] pt-3 pb-1.5 text-[11.5px] font-semibold text-text-2">
            <span>9:41</span>
            <span class="num">{{ label }}</span>
          </div>
          <img :src="src" alt="" aria-hidden="true" loading="eager" fetchpriority="high" class="block w-full dark:hidden" />
          <img :src="srcDark" alt="" aria-hidden="true" loading="eager" fetchpriority="high" class="hidden w-full dark:block" />
        </div>
      </div>
    </div>
  </div>
</template>
