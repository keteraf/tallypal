<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';
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
        <div class="mx-auto w-full max-w-330 px-6 py-10 md:px-8">
            <h1 class="m-0 mb-8 font-serif text-[clamp(36px,5vw,48px)] leading-[1.05] tracking-[-0.02em]">
                {{ $t('tallies.create_lead') }}
                <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ $t('tallies.create_em') }}</em>
            </h1>

            <div class="max-w-220 rounded-sm border border-rule bg-background px-9 pt-9 pb-7">
                <Form
                    :action="route('tallies.store')"
                    method="post"
                    :defaults="{
                        name: '',
                        description: '',
                    }"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-7">
                        <div class="flex flex-col gap-2">
                            <label for="name" class="eyebrow">{{ $t('tallies.name_label') }}</label>
                            <Input id="name" name="name" :placeholder="$t('tallies.name_placeholder')" required autofocus />
                            <div v-if="errors.name" class="text-sm font-medium text-destructive">{{ errors.name }}</div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="eyebrow">{{ $t('tallies.description_label') }}</label>
                            <Textarea id="description" name="description" :placeholder="$t('tallies.description_placeholder')" />
                            <div v-if="errors.description" class="text-sm font-medium text-destructive">{{ errors.description }}</div>
                        </div>
                    </div>

                    <div class="mt-9 flex justify-end gap-3 border-t border-rule-2 pt-6">
                        <Button variant="ghost" as-child>
                            <Link href="/tallies">{{ $t('common.cancel') }}</Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? $t('common.creating') : $t('tallies.create_title') }} →
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </WebLayout>
</template>
