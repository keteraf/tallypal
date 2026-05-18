<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as profile from '@/routes/profile';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(profile.destroy.url(), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall :title="$t('common.delete') + ' account'" :description="$t('settings.profile.delete_description')" />
        <div class="space-y-4 rounded-sm border border-neg/30 bg-neg/[0.05] px-5 py-4">
            <div class="space-y-1 text-neg">
                <p class="font-mono text-[11px] tracking-[0.12em] uppercase">{{ $t('common.warning') }}</p>
                <p class="text-sm">{{ $t('settings.profile.delete_warning') }}</p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive">{{ $t('common.delete') }} account</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('settings.profile.delete_confirm_title') }}</DialogTitle>
                        <DialogDescription>{{ $t('settings.profile.delete_confirm_description') }}</DialogDescription>
                    </DialogHeader>
                    <form class="space-y-4 px-7 py-5" @submit="deleteUser">
                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">{{ $t('common.password') }}</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                ref="passwordInput"
                                v-model="form.password"
                                :placeholder="$t('common.placeholder.password')"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <DialogFooter class="mx-0 -mb-1 border-0 bg-transparent px-0 py-0">
                            <DialogClose as-child>
                                <Button variant="ghost" type="button" @click="closeModal"> {{ $t('common.cancel') }} </Button>
                            </DialogClose>

                            <Button type="submit" variant="destructive" :disabled="form.processing"> {{ $t('common.delete') }} account </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
