<script setup lang="ts">
withDefaults(
  defineProps<{
    label: string
    value: string
    tone?: "pos" | "neutral"
    /** Which side of the device frame this overlaps (source `.float.f1`/`.f2`). */
    position: "left" | "right"
  }>(),
  {
    tone: "neutral",
  },
)
</script>

<template>
  <div
    class="absolute z-[3] hidden w-[158px] rounded-card border border-line bg-surface px-[15px] py-[13px] shadow-float min-[820px]:block"
    :class="position === 'left' ? '-mr-[18px] top-[26%] right-full' : '-ml-[18px] bottom-[14%] left-full'"
  >
    <div class="text-[10.5px] font-semibold tracking-[0.07em] whitespace-nowrap text-text-3 uppercase">{{ label }}</div>
    <div class="num mt-[5px] text-float-v font-semibold" :class="tone === 'pos' ? 'text-pos' : 'text-text'">{{ value }}</div>
  </div>
</template>
