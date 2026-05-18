<script setup lang="ts">
import { DropdownMenuItem, DropdownMenuPortal, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger } from '@/components/ui/dropdown-menu';
import { useLanguageSwitcher } from '@/composables/useLanguageSwitcher';
import { Check, Languages } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { languages, locale, setLocale } = useLanguageSwitcher();
</script>

<template>
    <DropdownMenuSub>
        <DropdownMenuSubTrigger>
            <Languages class="h-4 w-4" />
            <span>{{ t('user_menu.language') }}</span>
        </DropdownMenuSubTrigger>
        <DropdownMenuPortal>
            <DropdownMenuSubContent>
                <DropdownMenuItem v-for="lang in languages" :key="lang.code" @click="setLocale(lang.code)" class="flex items-center justify-between">
                    {{ lang.name }}
                    <Check v-if="locale === lang.code" class="ml-2 h-4 w-4" />
                </DropdownMenuItem>
            </DropdownMenuSubContent>
        </DropdownMenuPortal>
    </DropdownMenuSub>
</template>
