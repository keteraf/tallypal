<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppHead from '@/components/seo/AppHead.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import * as password from '@/routes/password';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(password.store.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.reset_password.title')" :description="$t('auth.reset_password.description')">
        <AppHead :title="$t('auth.reset_password.head_title')" :noindex="true" />

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">{{ $t('common.email') }}</Label>
                    <Input id="email" type="email" name="email" variant="filled" autocomplete="email" v-model="form.email" readonly />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{ $t('common.password') }}</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        variant="filled"
                        autocomplete="new-password"
                        v-model="form.password"
                        autofocus
                        :placeholder="$t('common.placeholder.password')"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation"> {{ $t('common.confirm_password') }} </Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        variant="filled"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        :placeholder="$t('common.placeholder.confirm_password')"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" size="touch" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ $t('auth.reset_password.reset_button') }}
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
