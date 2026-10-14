<script setup lang="ts">
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

/**
 * Password-confirmation dialog that deletes the account. The trigger is supplied by the
 * parent through the default slot (`DeleteUser` card, You hub row).
 */
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
    <Dialog>
        <DialogTrigger as-child>
            <slot />
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
                        variant="filled"
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
</template>
