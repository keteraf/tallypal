<script setup lang="ts">
import logoDark from '../../images/logo-dark.svg';
import logoLight from '../../images/logo.svg';

interface Props {
    variant?: 'auto' | 'light' | 'dark';
}

withDefaults(defineProps<Props>(), {
    variant: 'auto',
});
</script>

<template>
    <span class="inline-block leading-none">
        <img v-if="variant === 'light'" :src="logoLight" alt="tallypal" class="block size-full" />
        <img v-else-if="variant === 'dark'" :src="logoDark" alt="tallypal" class="block size-full" />
        <template v-else>
            <img :src="logoLight" alt="tallypal" class="block size-full dark:hidden" />
            <img :src="logoDark" alt="tallypal" class="hidden size-full dark:block" />
        </template>
    </span>
</template>
