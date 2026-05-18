<script setup lang="ts" generic="T extends string | number">
import { computed, type HTMLAttributes, type Ref } from 'vue';
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils';

interface SegmentOption<TValue extends string | number> {
    value: TValue;
    label: string;
    accent?: 'default' | 'positive';
}

const props = defineProps<{
    modelValue?: T;
    defaultValue?: T;
    options: SegmentOption<T>[];
    class?: HTMLAttributes['class'];
    name?: string;
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', value: T): void;
}>();

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const current = useVModel(props as any, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
}) as Ref<T | undefined>;

const selectedValue = computed<T | undefined>(() => current.value ?? props.defaultValue);

const selectClass = (option: SegmentOption<T>) => {
    const isActive = selectedValue.value === option.value;

    if (!isActive) {
        return 'text-muted-foreground hover:text-foreground';
    }

    if (option.accent === 'positive') {
        return 'bg-pos text-paper';
    }

    return 'bg-primary text-primary-foreground';
};

const selectOption = (option: SegmentOption<T>) => {
    current.value = option.value;
};
</script>

<template>
    <div
        role="radiogroup"
        :class="cn(
            'inline-flex p-0.75 rounded-full border border-rule font-mono text-[11px] tracking-[0.06em] uppercase',
            props.class,
        )"
    >
        <button
            v-for="option in options"
            :key="String(option.value)"
            type="button"
            role="radio"
            :name="name"
            :aria-checked="selectedValue === option.value"
            :class="cn('appearance-none border-0 px-3.5 py-1.5 rounded-full cursor-pointer transition-all duration-200 outline-none', selectClass(option))"
            @click="selectOption(option)"
        >
            {{ option.label }}
        </button>
    </div>
</template>
