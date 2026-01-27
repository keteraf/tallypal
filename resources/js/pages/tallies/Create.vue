<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tallies',
        href: '/tallies',
    },
    {
        title: 'Create',
        href: '/tallies/create',
    },
];
</script>

<template>
    <Head title="Create Tally" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Create New Tally</h1>
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
                        <Button variant="outline" as="a" href="/tallies"> Cancel </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? 'Creating...' : 'Create Tally' }}
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </WebLayout>
</template>
