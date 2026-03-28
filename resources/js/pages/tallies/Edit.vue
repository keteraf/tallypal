<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, useForm, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    tally: {
        tally_id: number;
        name: string;
        description: string;
        owner_id: number;
    };
}>();

const page = usePage();
const { t } = useI18n();
const isOwner = computed(() => page.props.auth.user.id === props.tally.owner_id);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('tallies.title'),
        href: route('tallies.index'),
    },
    {
        title: props.tally.name,
        href: route('tallies.transactions.index', props.tally.tally_id),
    },
    {
        title: t('common.edit'),
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
    <Head :title="$t('tallies.edit_title', { name: tally.name })" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-[768px]:pb-24">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-center text-2xl font-bold sm:text-left">{{ $t('tallies.edit_heading') }}</h1>

                <Dialog v-if="isOwner" v-model:open="showDeleteDialog">
                    <DialogTrigger as-child>
                        <Button variant="destructive" class="hidden w-full min-[769px]:inline-flex sm:w-auto">
                            <Trash2 class="mr-2 h-4 w-4" />
                            {{ $t('tallies.delete_tally') }}
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>{{ $t('tallies.delete_confirm_title') }}</DialogTitle>
                            <DialogDescription>
                                {{ $t('tallies.delete_confirm_description', { name: tally.name }) }}
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button variant="outline" @click="showDeleteDialog = false">{{ $t('common.cancel') }}</Button>
                            <Button variant="destructive" :disabled="deleteForm.processing" @click="deleteTally">
                                {{ deleteForm.processing ? $t('common.deleting') : $t('tallies.delete_tally') }}
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
                        <Label for="name">{{ $t('tallies.name_label') }}</Label>
                        <Input id="name" name="name" :default-value="tally.name" :placeholder="$t('tallies.name_placeholder')" required autofocus />
                        <div v-if="errors.name" class="text-sm font-medium text-destructive">
                            {{ errors.name }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ $t('tallies.description_label') }}</Label>
                        <Textarea
                            id="description"
                            name="description"
                            :default-value="tally.description"
                            :placeholder="$t('tallies.description_placeholder')"
                            class="min-h-[100px]"
                        />
                        <div v-if="errors.description" class="text-sm font-medium text-destructive">
                            {{ errors.description }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button variant="outline" as-child>
                            <a :href="route('tallies.transactions.index', tally.tally_id)"> {{ $t('common.cancel') }} </a>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? $t('common.updating') : $t('common.update') + ' Tally' }}
                        </Button>
                    </div>
                </Form>
            </div>
        </div>

        <div
            v-if="isOwner"
            class="fixed inset-x-0 bottom-0 z-40 border-t bg-background/95 p-3 backdrop-blur min-[769px]:hidden"
            style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)"
        >
            <Dialog v-model:open="showDeleteDialog">
                <DialogTrigger as-child>
                    <Button variant="destructive" class="w-full sm:w-auto">
                        <Trash2 class="mr-2 h-4 w-4" />
                        {{ $t('tallies.delete_tally') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('tallies.delete_confirm_title') }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('tallies.delete_confirm_description', { name: tally.name }) }}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" @click="showDeleteDialog = false">{{ $t('common.cancel') }}</Button>
                        <Button variant="destructive" :disabled="deleteForm.processing" @click="deleteTally">
                            {{ deleteForm.processing ? $t('common.deleting') : $t('tallies.delete_tally') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </WebLayout>
</template>
