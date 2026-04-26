<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    tally: {
        tally_id: number;
        uuid: string;
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
        href: route('tallies.transactions.index', props.tally.uuid),
    },
    {
        title: t('common.edit'),
        href: route('tallies.edit', props.tally.uuid),
    },
];

const deleteForm = useForm({});
const showDeleteDialog = ref(false);

const deleteTally = () => {
    deleteForm.delete(route('tallies.destroy', props.tally.uuid), {
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
    });
};
</script>

<template>
    <Head :title="$t('tallies.edit_title', { name: tally.name })" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1320px] px-6 py-10 max-[768px]:pb-28 md:px-8">
            <div class="mb-4 flex items-center gap-2 font-mono text-[11px] tracking-[0.1em] text-muted-foreground uppercase">
                <Link :href="route('tallies.index')" class="transition-colors hover:text-foreground">{{ $t('tallies.title') }}</Link>
                <span class="opacity-60">›</span>
                <Link :href="route('tallies.transactions.index', tally.uuid)" class="transition-colors hover:text-foreground">{{ tally.name }}</Link>
                <span class="opacity-60">›</span>
                <span class="text-foreground">{{ $t('common.edit') }}</span>
            </div>

            <div class="mb-8 flex flex-col gap-3 border-b border-rule pb-6 sm:flex-row sm:items-end sm:justify-between">
                <h1 class="m-0 font-serif text-[clamp(36px,5vw,48px)] leading-[1.05] tracking-[-0.02em]">
                    {{ $t('tallies.edit_lead') }}
                    <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ tally.name }}</em>
                </h1>

                <Dialog v-if="isOwner" v-model:open="showDeleteDialog">
                    <DialogTrigger as-child>
                        <Button variant="ghost" class="hidden border-neg/40 text-neg hover:border-neg min-[769px]:inline-flex">
                            <Trash2 class="size-4" />
                            {{ $t('tallies.delete_tally') }}
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>{{ $t('tallies.delete_confirm_title') }}</DialogTitle>
                            <DialogDescription>{{ $t('tallies.delete_confirm_description', { name: tally.name }) }}</DialogDescription>
                        </DialogHeader>
                        <div class="px-7 py-5"></div>
                        <DialogFooter>
                            <Button variant="ghost" @click="showDeleteDialog = false">{{ $t('common.cancel') }}</Button>
                            <Button variant="destructive" :disabled="deleteForm.processing" @click="deleteTally">
                                {{ deleteForm.processing ? $t('common.deleting') : $t('tallies.delete_tally') }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="max-w-[880px] rounded-sm border border-rule bg-background px-9 pt-9 pb-7">
                <Form
                    :action="route('tallies.update', tally.uuid)"
                    method="put"
                    :defaults="{
                        name: tally.name,
                        description: tally.description,
                    }"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-7">
                        <div class="flex flex-col gap-2">
                            <label for="name" class="eyebrow">{{ $t('tallies.name_label') }}</label>
                            <Input
                                id="name"
                                name="name"
                                :default-value="tally.name"
                                :placeholder="$t('tallies.name_placeholder')"
                                required
                                autofocus
                            />
                            <div v-if="errors.name" class="text-sm font-medium text-destructive">{{ errors.name }}</div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="eyebrow">{{ $t('tallies.description_label') }}</label>
                            <Textarea
                                id="description"
                                name="description"
                                :default-value="tally.description"
                                :placeholder="$t('tallies.description_placeholder')"
                            />
                            <div v-if="errors.description" class="text-sm font-medium text-destructive">{{ errors.description }}</div>
                        </div>
                    </div>

                    <div class="mt-9 flex justify-end gap-3 border-t border-rule-2 pt-6">
                        <Button variant="ghost" as-child>
                            <Link :href="route('tallies.transactions.index', tally.uuid)">{{ $t('common.cancel') }}</Link>
                        </Button>
                        <Button type="submit" :disabled="processing"> {{ processing ? $t('common.updating') : $t('common.update') }} → </Button>
                    </div>
                </Form>
            </div>
        </div>

        <div
            v-if="isOwner"
            class="fixed inset-x-0 bottom-0 z-40 border-t border-rule bg-background/95 p-3 backdrop-blur min-[769px]:hidden"
            style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)"
        >
            <Dialog v-model:open="showDeleteDialog">
                <DialogTrigger as-child>
                    <Button variant="destructive" class="w-full">
                        <Trash2 class="size-4" />
                        {{ $t('tallies.delete_tally') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ $t('tallies.delete_confirm_title') }}</DialogTitle>
                        <DialogDescription>{{ $t('tallies.delete_confirm_description', { name: tally.name }) }}</DialogDescription>
                    </DialogHeader>
                    <div class="px-7 py-5"></div>
                    <DialogFooter>
                        <Button variant="ghost" @click="showDeleteDialog = false">{{ $t('common.cancel') }}</Button>
                        <Button variant="destructive" :disabled="deleteForm.processing" @click="deleteTally">
                            {{ deleteForm.processing ? $t('common.deleting') : $t('tallies.delete_tally') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </WebLayout>
</template>
