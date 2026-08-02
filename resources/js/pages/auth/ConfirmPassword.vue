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

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(password.confirm.url(), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.confirm_password.title')" :description="$t('auth.confirm_password.description')">
        <AppHead :title="$t('auth.confirm_password.head_title')" :noindex="true" />

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label for="password">{{ $t('common.password') }}</Label>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        variant="filled"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        autofocus
                        :placeholder="$t('common.placeholder.password')"
                    />

                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center">
                    <Button size="touch" class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ $t('auth.confirm_password.confirm_button') }}
                    </Button>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
