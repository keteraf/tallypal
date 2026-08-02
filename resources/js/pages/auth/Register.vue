<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppHead from '@/components/seo/AppHead.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login, register } from '@/routes';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(register.url(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase :title="$t('auth.register.title')" :description="$t('auth.register.description')">
        <AppHead :title="$t('auth.register.head_title')" :noindex="true" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">{{ $t('common.name') }}</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        variant="filled"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        v-model="form.name"
                        :placeholder="$t('common.placeholder.full_name')"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">{{ $t('common.email') }}</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        variant="filled"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        v-model="form.email"
                        :placeholder="$t('common.placeholder.email')"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{ $t('common.password') }}</Label>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        variant="filled"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password"
                        :placeholder="$t('common.placeholder.password')"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">{{ $t('common.confirm_password') }}</Label>
                    <Input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        variant="filled"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        :placeholder="$t('common.placeholder.confirm_password')"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" size="touch" class="mt-2 w-full" :tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ $t('auth.register.create_account_button') }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ $t('auth.register.already_have_account') }}
                <TextLink :href="login.url()" :tabindex="6">{{ $t('auth.register.login') }}</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
