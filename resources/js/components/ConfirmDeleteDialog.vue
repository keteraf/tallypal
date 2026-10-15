<script setup lang="ts">
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

/**
 * Confirmation dialog for deleting a record (tally, transaction). The trigger is supplied by the
 * parent through the default slot; `confirm` fires only from the destructive button.
 */
defineProps<{
    title: string;
    description: string;
    /** Extra warning shown under the description, e.g. the tally is shared. */
    notice?: string | null;
    confirmLabel: string;
    processing?: boolean;
}>();

defineEmits<{ confirm: [] }>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <slot />
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
                <p v-if="notice" class="text-sm font-medium text-neg">{{ notice }}</p>
            </DialogHeader>
            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="ghost" type="button">{{ $t('common.cancel') }}</Button>
                </DialogClose>
                <Button variant="destructive" type="button" :disabled="processing" @click="$emit('confirm')">
                    {{ processing ? $t('common.deleting') : confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
