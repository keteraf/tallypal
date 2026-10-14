<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { logout } from '@/routes';
import * as password from '@/routes/password';
import * as preferences from '@/routes/preferences';
import * as profile from '@/routes/profile';

import DeleteAccountDialog from '@/components/DeleteAccountDialog.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { CardHeader } from '@/components/ui/card-header';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { SettingRow } from '@/components/ui/setting-row';
import { useAppearance } from '@/composables/useAppearance';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import { type User } from '@/types';

type Appearance = 'light' | 'dark' | 'system';

const user = computed(() => usePage().props.auth.user as User);
const { getInitials } = useInitials();
const { appearance, updateAppearance } = useAppearance();

const themes: Appearance[] = ['light', 'dark', 'system'];

const currencies = computed(() => usePage().props.currencies ?? []);
const NO_CURRENCY = 'none';
const defaultCurrencyValue = computed(() => user.value.default_currency?.uuid ?? NO_CURRENCY);

/** Saves on change, one row at a time; the shared `auth.user.default_currency` prop refreshes with the redirect. */
const updateDefaultCurrency = (uuid: unknown) => {
    router.patch(
        preferences.update.url(),
        { default_currency_uuid: uuid === NO_CURRENCY ? null : String(uuid) },
        { preserveScroll: true, preserveState: true },
    );
};

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <AppLayout>
        <Head :title="$t('settings.title')" />

        <div class="mx-auto w-full max-w-330 px-5 py-6 pb-28 md:px-8 md:py-8">
            <div class="mb-[18px] md:mb-[26px]">
                <h1 class="text-[24px] font-semibold tracking-[-0.03em] text-text sm:text-[26px]">{{ $t('settings.title') }}</h1>
                <p class="mt-[3px] text-[13px] text-text-3">{{ $t('settings.subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 gap-3 min-[1101px]:grid-cols-12 min-[1101px]:gap-4">
                <section class="flex items-center gap-4 self-start rounded-card bg-surface p-4 min-[1101px]:col-span-5 sm:p-5">
                    <Avatar size="xl">
                        <AvatarFallback color="c1">{{ getInitials(user.name) }}</AvatarFallback>
                    </Avatar>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[17px] font-semibold tracking-[-0.02em] text-text">{{ user.name }}</div>
                        <div class="mt-0.5 truncate text-[13px] text-text-3">{{ user.email }}</div>
                    </div>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="profile.edit.url()">{{ $t('common.edit') }}</Link>
                    </Button>
                </section>

                <div class="flex flex-col gap-3 min-[1101px]:col-span-7 min-[1101px]:gap-4">
                    <section class="rounded-card bg-surface p-4 sm:p-5">
                        <CardHeader :title="$t('settings.you.preferences')" />
                        <div>
                            <SettingRow :title="$t('settings.you.theme')" :description="$t('settings.you.theme_description')">
                                <template #control>
                                    <Select :model-value="appearance" @update:model-value="updateAppearance($event as Appearance)">
                                        <SelectTrigger variant="pill" :aria-label="$t('settings.you.theme')">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="theme in themes" :key="theme" :value="theme">
                                                {{ $t(`settings.appearance.${theme}`) }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </template>
                            </SettingRow>
                            <SettingRow :title="$t('settings.you.default_currency')" :description="$t('settings.you.default_currency_description')">
                                <template #control>
                                    <Select :model-value="defaultCurrencyValue" @update:model-value="updateDefaultCurrency">
                                        <SelectTrigger variant="pill" :aria-label="$t('settings.you.default_currency')">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="NO_CURRENCY">{{ $t('settings.you.default_currency_none') }}</SelectItem>
                                            <SelectItem v-for="currency in currencies" :key="currency.uuid" :value="currency.uuid">
                                                {{ currency.code }} {{ currency.symbol }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </template>
                            </SettingRow>
                        </div>
                    </section>

                    <section class="rounded-card bg-surface p-4 sm:p-5">
                        <CardHeader :title="$t('settings.you.account')" />
                        <div>
                            <SettingRow
                                :title="$t('settings.you.password')"
                                :description="$t('settings.you.password_description')"
                                :href="password.edit.url()"
                            />
                        </div>
                    </section>

                    <section class="rounded-card bg-surface p-4 sm:p-5">
                        <CardHeader :title="$t('settings.you.data')" />
                        <div>
                            <SettingRow tone="neg" :title="$t('settings.you.sign_out')" :description="$t('settings.you.sign_out_description')">
                                <template #control>
                                    <Button variant="outline" size="sm" as-child class="text-neg">
                                        <Link method="post" :href="logout.url()" as="button" @click="handleLogout">{{
                                            $t('settings.you.sign_out')
                                        }}</Link>
                                    </Button>
                                </template>
                            </SettingRow>
                            <SettingRow tone="neg" :title="$t('settings.you.delete_account')" :description="$t('settings.you.delete_description')">
                                <template #control>
                                    <DeleteAccountDialog>
                                        <Button variant="outline" size="sm" class="text-neg">{{ $t('settings.you.delete_account') }}</Button>
                                    </DeleteAccountDialog>
                                </template>
                            </SettingRow>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
