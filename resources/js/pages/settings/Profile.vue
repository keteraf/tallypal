<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

import { logout } from '@/routes';
import * as profile from '@/routes/profile';
import * as verification from '@/routes/verification';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const handleLogout = () => {
    router.flushAll();
};

const submit = () => {
    form.patch(profile.update.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="$t('settings.profile.title')" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title="$t('settings.profile.heading')" :description="$t('settings.profile.description')" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">{{ $t('common.name') }}</Label>
                        <Input
                            id="name"
                            variant="filled"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autocomplete="name"
                            :placeholder="$t('common.placeholder.full_name')"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">{{ $t('common.email') }}</Label>
                        <Input
                            id="email"
                            type="email"
                            variant="filled"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            :placeholder="$t('common.placeholder.email')"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            {{ $t('settings.profile.unverified_email') }}
                            <Link
                                :href="verification.send.url()"
                                method="post"
                                as="button"
                                class="text-text underline decoration-line underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current!"
                            >
                                {{ $t('settings.profile.resend_verification') }}
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-pos">
                            {{ $t('settings.profile.verification_sent') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ $t('common.save') }}</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-text-3">{{ $t('common.saved') }}</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <HeadingSmall :title="$t('settings.profile.sign_out_heading')" :description="$t('settings.profile.sign_out_description')" />
                <Button variant="outline" as-child class="text-neg">
                    <Link method="post" :href="logout.url()" as="button" @click="handleLogout">{{ $t('settings.profile.sign_out') }}</Link>
                </Button>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
