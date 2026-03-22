<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import { Loader2, Plus, Share2, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Collaborator {
    id: number;
    name: string;
    email: string;
    percentage: number;
}

const props = defineProps<{
    open: boolean;
    tally: {
        tally_id: number;
        name: string;
        owner_id: number;
    } | null;
}>();

const emit = defineEmits(['update:open']);

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const collaborators = ref<Collaborator[]>([]);
const isLoading = ref(false);
const isInviting = ref(false);
const isSaving = ref(false);
const newCollaboratorEmail = ref('');
const errors = ref<Record<string, string[]>>({});

const fetchCollaborators = async () => {
    if (!props.tally) return;

    isLoading.value = true;
    errors.value = {};
    try {
        const response = await fetch(route('api.tallies.shares.index', props.tally.tally_id));
        if (response.ok) {
            const result = await response.json();
            collaborators.value = result.data;
        }
    } catch (e) {
        console.error('Failed to fetch collaborators', e);
    } finally {
        isLoading.value = false;
    }
};

const addCollaborator = async () => {
    if (!props.tally || !newCollaboratorEmail.value) return;

    isInviting.value = true;
    errors.value = {};
    try {
        const response = await fetch(route('api.tallies.shares.store', props.tally.tally_id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({ email: newCollaboratorEmail.value }),
        });

        if (response.ok) {
            newCollaboratorEmail.value = '';
            await fetchCollaborators();
        } else if (response.status === 422) {
            const data = await response.json();
            errors.value = data.errors;
        }
    } catch (e) {
        console.error('Failed to add collaborator', e);
    } finally {
        isInviting.value = false;
    }
};

const removeCollaborator = async (collaboratorId: number) => {
    if (!props.tally) return;

    try {
        const response = await fetch(route('api.tallies.shares.destroy', { tally: props.tally.tally_id, share: collaboratorId }), {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
        });

        if (response.ok) {
            await fetchCollaborators();
        }
    } catch (e) {
        console.error('Failed to remove collaborator', e);
    }
};

const totalPercentage = computed(() => {
    return collaborators.value.reduce((sum, c) => sum + (Number(c.percentage) || 0), 0);
});

const isPercentageValid = computed(() => totalPercentage.value === 100);

const saveShares = async () => {
    if (!props.tally || !isPercentageValid.value) return;

    isSaving.value = true;
    errors.value = {};
    try {
        const response = await fetch(route('api.tallies.shares.update', props.tally.tally_id), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                shares: collaborators.value.map((c) => ({
                    user_id: c.id,
                    percentage: c.percentage,
                })),
            }),
        });

        if (response.ok) {
            // Maybe show success message?
            isOpen.value = false;
        } else if (response.status === 422) {
            const data = await response.json();
            errors.value = data.errors;
        }
    } catch (e) {
        console.error('Failed to save shares', e);
    } finally {
        isSaving.value = false;
    }
};

watch(
    () => props.open,
    (newVal) => {
        if (newVal && props.tally) {
            fetchCollaborators();
        }
    },
);

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase();
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Share2 class="h-5 w-5" />
                    {{ $t('tallies.share.title', { name: tally?.name }) }}
                </DialogTitle>
                <DialogDescription>
                    {{ $t('tallies.share.description') }}
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4 sm:gap-6">
                <div class="space-y-2">
                    <Label for="email">{{ $t('tallies.share.invite_collaborator') }}</Label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Input
                            id="email"
                            v-model="newCollaboratorEmail"
                            :placeholder="$t('common.placeholder.email')"
                            type="email"
                            :disabled="isInviting"
                            class="flex-1"
                            @keyup.enter="addCollaborator"
                        />
                        <Button :disabled="isInviting || !newCollaboratorEmail" class="w-full sm:w-auto" @click="addCollaborator">
                            <Loader2 v-if="isInviting" class="mr-2 h-4 w-4 animate-spin" />
                            <Plus v-else class="mr-2 h-4 w-4" />
                            {{ $t('tallies.share.invite_button') }}
                        </Button>
                    </div>
                    <InputError v-if="errors.email" :message="errors.email[0]" />
                </div>

                <div class="space-y-4">
                    <Label>{{ $t('tallies.share.collaborators') }}</Label>
                    <div v-if="isLoading" class="space-y-3">
                        <div v-for="i in 3" :key="i" class="flex items-center gap-3 border-b py-3 last:border-0">
                            <Skeleton class="h-8 w-8 shrink-0 rounded-full" />
                            <div class="min-w-0 flex-1 space-y-2">
                                <Skeleton class="h-4 w-3/4" />
                                <Skeleton class="h-3 w-1/2" />
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <Skeleton class="h-8 w-20" />
                                <Skeleton class="h-8 w-8" />
                            </div>
                        </div>
                    </div>
                    <div v-else-if="collaborators.length === 0" class="py-4 text-center text-sm text-gray-500">
                        {{ $t('tallies.share.no_collaborators') }}
                    </div>
                    <div v-else class="max-h-[300px] overflow-y-auto pr-2">
                        <div v-for="collaborator in collaborators" :key="collaborator.id" class="flex items-center gap-3 border-b py-3 last:border-0">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <Avatar class="h-8 w-8 shrink-0">
                                    <AvatarFallback>{{ getInitials(collaborator.name) }}</AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm leading-none font-medium">{{ collaborator.name }}</p>
                                    <p class="mt-1 truncate text-xs text-gray-500">{{ collaborator.email }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <div class="relative w-20">
                                    <Input v-model.number="collaborator.percentage" type="number" class="h-8 pr-6 text-right" min="0" max="100" />
                                    <span class="absolute top-1.5 right-2 text-xs text-gray-500">%</span>
                                </div>
                                <div class="flex w-8 justify-center">
                                    <Button
                                        v-if="collaborator.id !== tally?.owner_id"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                        @click="removeCollaborator(collaborator.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <InputError v-if="errors.shares" :message="errors.shares[0]" />
                </div>
            </div>

            <DialogFooter class="flex flex-col items-center justify-between gap-4 border-t pt-4 sm:flex-row sm:gap-0">
                <div class="flex w-full flex-col items-center sm:w-auto sm:items-start">
                    <span class="text-sm font-medium" :class="isPercentageValid ? 'text-green-600' : 'text-red-600'">
                        {{ $t('tallies.share.total', { total: totalPercentage }) }}
                    </span>
                    <span v-if="!isPercentageValid" class="text-[10px] text-red-500">
                        {{ $t('tallies.share.must_equal_100') }}
                    </span>
                </div>
                <div class="flex w-full gap-2 sm:w-auto">
                    <Button variant="outline" class="flex-1 sm:flex-none" @click="isOpen = false">{{ $t('common.cancel') }}</Button>
                    <Button :disabled="!isPercentageValid || isSaving || isLoading" class="flex-1 sm:flex-none" @click="saveShares">
                        <Loader2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                        {{ $t('tallies.share.save_changes') }}
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
