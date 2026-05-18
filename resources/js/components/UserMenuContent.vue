<script setup lang="ts">
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import * as profile from '@/routes/profile';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link :href="profile.edit.url()" prefetch as="button">
                <Settings class="h-4 w-4" />
                {{ $t('user_menu.settings') }}
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <LanguageSwitcher />
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link method="post" :href="logout.url()" @click="handleLogout" as="button">
            <LogOut class="h-4 w-4" />
            {{ $t('user_menu.logout') }}
        </Link>
    </DropdownMenuItem>
</template>
