<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, useForm, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    tally: {
        tally_id: number;
        name: string;
        description: string;
        owner_id: number;
    };
}>();

const page = usePage();
const isOwner = computed(() => page.props.auth.user.id === props.tally.owner_id);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tallies',
        href: route('tallies.index'),
    },
    {
        title: props.tally.name,
        href: route('tallies.transactions.index', props.tally.tally_id),
    },
    {
        title: 'Edit',
        href: route('tallies.edit', props.tally.tally_id),
    },
];

const deleteForm = useForm({});
const showDeleteDialog = ref(false);

const deleteTally = () => {
    deleteForm.delete(route('tallies.destroy', props.tally.tally_id), {
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Edit Tally - ${tally.name}`" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h1 class="text-2xl font-bold text-center sm:text-left">Edit Tally</h1>

                <Dialog v-if="isOwner" v-model:open="showDeleteDialog">
                    <DialogTrigger as-child>
                        <Button variant="destructive" class="w-full sm:w-auto">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Tally
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Are you sure you want to delete this tally?</DialogTitle>
                            <DialogDescription>
                                This action cannot be undone. This will permanently delete the tally "{{ tally.name }}" and all its transactions.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                            <Button variant="destructive" :disabled="deleteForm.processing" @click="deleteTally">
                                {{ deleteForm.processing ? 'Deleting...' : 'Delete Tally' }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="max-w-2xl rounded-lg border p-6 shadow-sm">
                <Form
                    :action="route('tallies.update', tally.tally_id)"
                    method="put"
                    :defaults="{
                        name: tally.name,
                        description: tally.description,
                    }"
                    #default="{ errors, processing }"
                    class="space-y-6"
                >
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" placeholder="Enter tally name" required autofocus />
                        <div v-if="errors.name" class="text-sm font-medium text-destructive">
                            {{ errors.name }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" name="description" placeholder="Optional description for this tally" class="min-h-[100px]" />
                        <div v-if="errors.description" class="text-sm font-medium text-destructive">
                            {{ errors.description }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button variant="outline" as="a" :href="route('tallies.transactions.index', tally.tally_id)"> Cancel </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? 'Updating...' : 'Update Tally' }}
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </WebLayout>
</template>
