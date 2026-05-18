<script setup lang="ts">
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const breadcrumbs = computed(() => props.breadcrumbs ?? []);
</script>

<template>
    <Breadcrumb>
        <BreadcrumbList class="gap-2 font-mono text-[11px] tracking-widest text-muted-foreground uppercase">
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <BreadcrumbItem>
                    <template v-if="index === breadcrumbs.length - 1">
                        <BreadcrumbPage class="font-medium text-foreground">{{ item.title }}</BreadcrumbPage>
                    </template>
                    <template v-else>
                        <BreadcrumbLink as-child>
                            <Link :href="item.href ?? '#'" class="transition-colors hover:text-foreground">{{ item.title }}</Link>
                        </BreadcrumbLink>
                    </template>
                </BreadcrumbItem>
                <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1">
                    <span class="text-muted-foreground/60">›</span>
                </BreadcrumbSeparator>
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
