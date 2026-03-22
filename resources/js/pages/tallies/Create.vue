<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('tallies.title'),
        href: '/tallies',
    },
    {
        title: t('common.create'),
        href: '/tallies/create',
    },
];
</script>

<template>
    <Head :title="$t('tallies.create_title')" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ $t('tallies.create_heading') }}</h1>
            </div>

            <div class="max-w-2xl rounded-lg border p-6 shadow-sm">
                <Form
                    :action="route('tallies.store')"
                    method="post"
                    :defaults="{
                        name: '',
                        description: '',
                    }"
                    #default="{ errors, processing }"
                    class="space-y-6"
                >
                    <div class="space-y-2">
                        <Label for="name">{{ $t('tallies.name_label') }}</Label>
                        <Input id="name" name="name" :placeholder="$t('tallies.name_placeholder')" required autofocus />
                        <div v-if="errors.name" class="text-sm font-medium text-destructive">
                            {{ errors.name }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ $t('tallies.description_label') }}</Label>
                        <Textarea id="description" name="description" :placeholder="$t('tallies.description_placeholder')" class="min-h-[100px]" />
                        <div v-if="errors.description" class="text-sm font-medium text-destructive">
                            {{ errors.description }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button variant="outline" as-child>
                            <a href="/tallies"> {{ $t('common.cancel') }} </a>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? $t('common.creating') : $t('tallies.create_title') }}
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </WebLayout>
</template>
