<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppHead from '@/components/seo/AppHead.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login, register } from '@/routes';
import * as password from '@/routes/password';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(login.url(), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase :title="$t('auth.login.title')" :description="$t('auth.login.description')">
        <AppHead :title="$t('auth.login.head_title')" :noindex="true" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-pos">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">{{ $t('common.email') }}</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        variant="filled"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        :placeholder="$t('common.placeholder.email')"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">{{ $t('common.password') }}</Label>
                        <TextLink v-if="canResetPassword" :href="password.request.url()" class="text-sm" :tabindex="5">
                            {{ $t('auth.login.forgot_password') }}
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        variant="filled"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        :placeholder="$t('common.placeholder.password')"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model="form.remember" :tabindex="3" />
                        <span>{{ $t('auth.login.remember_me') }}</span>
                    </Label>
                </div>

                <Button type="submit" size="touch" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ $t('auth.login.login_button') }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ $t('auth.login.no_account') }}
                <TextLink :href="register.url()" :tabindex="5">{{ $t('auth.login.signup') }}</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
