<script setup lang="ts" generic="T extends string | number">
import { computed, type HTMLAttributes, type Ref } from 'vue';
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils';

interface SegmentOption<TValue extends string | number> {
    value: TValue;
    label: string;
    accent?: 'default' | 'positive';
}

const props = withDefaults(
    defineProps<{
        modelValue?: T;
        defaultValue?: T;
        options: SegmentOption<T>[];
        class?: HTMLAttributes['class'];
        name?: string;
        /** `default` = editorial pill; `mobile` = full-width surface track (design source); from `sm` (640px) it takes the design's desktop `.seg` size (13px, 16px padding). */
        variant?: 'default' | 'mobile';
        /** `sm` = the split editor's `.seg-s` (inline track, 2px padding, 12px labels); `mobile` variant only. */
        size?: 'default' | 'sm';
    }>(),
    {
        variant: 'default',
        size: 'default',
    },
);

const emits = defineEmits<{
    (e: 'update:modelValue', value: T): void;
}>();

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const current = useVModel(props as any, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
}) as Ref<T | undefined>;

const selectedValue = computed<T | undefined>(() => current.value ?? props.defaultValue);

const isMobile = computed(() => props.variant === 'mobile');

const isSmall = computed(() => isMobile.value && props.size === 'sm');

const containerClass = computed(() =>
    isSmall.value
        ? 'inline-flex gap-0.5 rounded-full bg-surface p-0.5'
        : isMobile.value
        ? 'flex w-full gap-0.5 rounded-full bg-surface p-[3px]'
        : 'inline-flex p-0.75 rounded-full border border-line font-mono text-[11px] tracking-[0.06em] uppercase',
);

const baseButtonClass = computed(() =>
    isSmall.value
        ? 'appearance-none border-0 px-3 py-1.5 rounded-full cursor-pointer transition-all duration-200 outline-none text-xs font-medium tracking-[-0.01em]'
        : isMobile.value
        ? 'flex-1 appearance-none border-0 px-1 py-2 rounded-full cursor-pointer transition-all duration-200 outline-none text-[12.5px] font-medium tracking-[-0.01em] sm:px-4 sm:text-[13px]'
        : 'appearance-none border-0 px-3.5 py-1.5 rounded-full cursor-pointer transition-all duration-200 outline-none',
);

const selectClass = (option: SegmentOption<T>): string => {
    const isActive = selectedValue.value === option.value;

    if (!isActive) {
        return isMobile.value ? 'text-text-3 hover:text-text' : 'text-muted-foreground hover:text-foreground';
    }

    if (isMobile.value) {
        return 'bg-surface-3 text-text';
    }

    if (option.accent === 'positive') {
        return 'bg-pos text-accent-ink';
    }

    return 'bg-primary text-primary-foreground';
};

const selectOption = (option: SegmentOption<T>) => {
    current.value = option.value;
};
</script>

<template>
    <div role="radiogroup" :class="cn(containerClass, props.class)">
        <button
            v-for="option in options"
            :key="String(option.value)"
            type="button"
            role="radio"
            :name="name"
            :aria-checked="selectedValue === option.value"
            :class="cn(baseButtonClass, selectClass(option))"
            @click="selectOption(option)"
        >
            {{ option.label }}
        </button>
    </div>
</template>
