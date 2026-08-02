<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    items: NavItem[];
    label?: string;
}>();

const page = usePage();

const currentPath = computed(() => page.url.split('?')[0]);

// A tab is active on its own route and any nested route (e.g. Tallies on a tally detail).
const isActive = (href: string): boolean => currentPath.value === href || currentPath.value.startsWith(`${href}/`);
</script>

<template>
    <SidebarGroup class="px-2 py-0 group-data-[collapsible=icon]:px-2.5">
        <SidebarGroupLabel v-if="label" class="font-mono text-[11px] tracking-[0.14em] text-text-3 uppercase">
            {{ label }}
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton v-if="item.disabled" :tooltip="item.title" :aria-disabled="true" class="cursor-default text-text-3 opacity-40">
                    <component :is="item.icon" />
                    <span>{{ item.title }}</span>
                </SidebarMenuButton>
                <SidebarMenuButton
                    v-else
                    as-child
                    :is-active="isActive(item.href)"
                    :tooltip="item.title"
                    class="text-text-2 hover:bg-surface data-[active=true]:bg-surface-2 data-[active=true]:font-medium data-[active=true]:text-text data-[active=true]:[&>svg]:text-accent"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span class="truncate">{{ item.title }}</span>
                        <span
                            v-if="item.kbd"
                            class="ml-auto shrink-0 rounded-[5px] border border-line bg-surface px-[5px] py-px font-mono text-[10.5px] text-text-3"
                        >
                            {{ item.kbd }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
