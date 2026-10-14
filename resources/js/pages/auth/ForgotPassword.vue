<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppHead from '@/components/seo/AppHead.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import * as password from '@/routes/password';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(password.email.url());
};
</script>

<template>
    <AuthLayout :title="$t('auth.forgot_password.title')" :description="$t('auth.forgot_password.description')">
        <AppHead :title="$t('auth.forgot_password.head_title')" :noindex="true" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-pos">
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="email">{{ $t('common.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        variant="filled"
                        autocomplete="email"
                        v-model="form.email"
                        autofocus
                        :placeholder="$t('common.placeholder.email')"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button size="touch" class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ $t('auth.forgot_password.email_button') }}
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>{{ $t('auth.forgot_password.return_to') }}</span>
                <TextLink :href="login.url()">{{ $t('auth.forgot_password.login') }}</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
