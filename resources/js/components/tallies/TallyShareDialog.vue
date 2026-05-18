<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import apiTallies from '@/routes/api/tallies';
import { useForm } from '@inertiajs/vue3';
import { Loader2, Plus, Share2, Trash2, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Collaborator {
    id: number;
    uuid: string;
    name: string;
    email: string;
    percentage: number;
}

interface PendingInvitation {
    uuid: string;
    email: string;
    status: string;
    expires_at: string | null;
    invited_by: { id: number; uuid: string; name: string; email: string };
}

const props = defineProps<{
    open: boolean;
    tally: {
        tally_id: number;
        uuid: string;
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
const pendingInvitations = ref<PendingInvitation[]>([]);
const isLoading = ref(false);
const isInviting = ref(false);
const isSaving = ref(false);
const isCancellingInvitation = ref<string | null>(null);
const newCollaboratorEmail = ref('');
const errors = ref<Record<string, string[]>>({});
const successMessage = ref('');

const inviteForm = useForm<{ email: string }>({ email: '' });

const getXsrfToken = (): string => {
    const name = 'XSRF-TOKEN=';
    const decodedCookie = decodeURIComponent(document.cookie);
    const parts = decodedCookie.split(';');
    for (let c of parts) {
        c = c.trim();
        if (c.startsWith(name)) {
            return c.substring(name.length);
        }
    }
    return '';
};

const fetchCollaborators = async () => {
    if (!props.tally) return;

    isLoading.value = true;
    errors.value = {};
    try {
        const [sharesResponse, invitationsResponse] = await Promise.all([
            fetch(apiTallies.shares.index.url(props.tally.uuid), { headers: { Accept: 'application/json' } }),
            fetch(apiTallies.invitations.index.url(props.tally.uuid), { headers: { Accept: 'application/json' } }),
        ]);

        if (sharesResponse.ok) {
            const result = await sharesResponse.json();
            collaborators.value = result.data;
        }

        if (invitationsResponse.ok) {
            const result = await invitationsResponse.json();
            pendingInvitations.value = result.data;
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
    successMessage.value = '';

    try {
        const response = await fetch(apiTallies.shares.store.url(props.tally.uuid), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            body: JSON.stringify({ email: newCollaboratorEmail.value }),
        });

        const result = await response.json();

        if (response.ok) {
            successMessage.value = t('tallies.share.invitation_sent', { email: newCollaboratorEmail.value });
            newCollaboratorEmail.value = '';
            await fetchCollaborators();
        } else {
            errors.value = result.errors || { email: [result.message] };
        }
    } catch (e) {
        console.error('Failed to invite collaborator', e);
    } finally {
        isInviting.value = false;
    }
};

const cancelInvitation = async (invitationUuid: string) => {
    if (!props.tally) return;

    isCancellingInvitation.value = invitationUuid;
    errors.value = {};

    try {
        const response = await fetch(
            apiTallies.invitations.destroy.url({
                tally: props.tally.uuid,
                invitation: invitationUuid as unknown as number,
            }),
            {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-XSRF-TOKEN': getXsrfToken() },
            },
        );

        if (response.ok) {
            await fetchCollaborators();
        } else {
            const result = await response.json();
            errors.value = result.errors || { general: [result.message || 'Failed to cancel invitation'] };
        }
    } catch (e) {
        console.error('Failed to cancel invitation', e);
    } finally {
        isCancellingInvitation.value = null;
    }
};

const removeCollaborator = async (collaboratorUuid: string) => {
    if (!props.tally) return;

    errors.value = {};

    try {
        const response = await fetch(
            apiTallies.shares.destroy.url({
                tally: props.tally.uuid,
                share: collaboratorUuid,
            }),
            {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-XSRF-TOKEN': getXsrfToken() },
            },
        );

        if (response.ok) {
            await fetchCollaborators();
        } else {
            const result = await response.json();
            errors.value = result.errors || { general: [result.message || 'Failed to remove collaborator'] };
        }
    } catch (e) {
        console.error('Failed to remove collaborator', e);
    }
};

const totalPercentage = computed(() => {
    const sum = collaborators.value.reduce((sum, c) => sum + (Number(c.percentage) || 0), 0);
    return Math.round((sum + Number.EPSILON) * 100) / 100;
});

const isPercentageValid = computed(() => totalPercentage.value === 100);

const saveShares = async () => {
    if (!props.tally || !isPercentageValid.value) return;

    isSaving.value = true;
    errors.value = {};

    const payload = {
        shares: collaborators.value.map((c) => ({
            user_id: c.id,
            percentage: c.percentage,
        })),
    };

    try {
        const response = await fetch(apiTallies.shares.update.url(props.tally.uuid), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            body: JSON.stringify(payload),
        });

        if (response.ok) {
            isOpen.value = false;
        } else {
            const result = await response.json();
            if (result.errors) {
                errors.value = result.errors;
            } else {
                errors.value = { general: [result.message || 'Failed to update shares'] };
            }
        }
    } catch (e) {
        console.error('Failed to update shares', e);
        errors.value = { general: ['An unexpected error occurred'] };
    } finally {
        isSaving.value = false;
    }
};

watch(
    () => props.open,
    (newVal) => {
        if (newVal && props.tally) {
            successMessage.value = '';
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
        <DialogContent class="sm:max-w-[520px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-3">
                    <span class="text-brand"><Share2 class="size-[18px]" /></span>
                    {{ $t('tallies.share.title_lead') }}
                    <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ tally?.name }}</em>
                </DialogTitle>
                <DialogDescription>{{ $t('tallies.share.description') }}</DialogDescription>
            </DialogHeader>

            <div class="px-7 py-6">
                <div class="mb-7 flex flex-col gap-2">
                    <label for="email" class="eyebrow">{{ $t('tallies.share.invite_collaborator') }}</label>
                    <div class="grid grid-cols-[1fr_auto] items-end gap-3">
                        <Input
                            id="email"
                            v-model="newCollaboratorEmail"
                            :placeholder="$t('common.placeholder.email')"
                            type="email"
                            :disabled="isInviting"
                            class="text-sm"
                            @keyup.enter="addCollaborator"
                        />
                        <Button
                            variant="ghost"
                            type="button"
                            :disabled="isInviting || inviteForm.processing || !newCollaboratorEmail"
                            @click="addCollaborator"
                        >
                            <Loader2 v-if="isInviting || inviteForm.processing" class="size-4 animate-spin" />
                            <Plus v-else class="size-4" />
                            {{ $t('tallies.share.invite_button') }}
                        </Button>
                    </div>
                    <InputError v-if="errors.email" :message="errors.email[0]" />
                    <p v-if="successMessage" class="text-sm text-pos">{{ successMessage }}</p>
                </div>

                <div v-if="isLoading || pendingInvitations.length > 0" class="mb-7">
                    <h6 class="eyebrow mb-3">{{ $t('tallies.share.pending_invitations') }}</h6>
                    <div v-if="isLoading" class="space-y-2">
                        <div
                            v-for="i in 2"
                            :key="i"
                            class="flex items-center justify-between gap-3 border-t border-rule-2 py-3 last:border-b last:border-rule-2"
                        >
                            <Skeleton class="h-4 w-40" />
                            <Skeleton class="h-7 w-7 rounded-full" />
                        </div>
                    </div>
                    <div v-else>
                        <div
                            v-for="invitation in pendingInvitations"
                            :key="invitation.uuid"
                            class="flex items-center justify-between gap-3 border-t border-rule-2 py-3 last:border-b last:border-rule-2"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <p class="truncate text-sm text-foreground">{{ invitation.email }}</p>
                                <span
                                    class="shrink-0 rounded-full border border-rule px-2 py-0.5 font-mono text-[10px] tracking-[0.1em] text-muted-foreground uppercase"
                                >
                                    {{ $t('tallies.share.invitation_pending_badge') }}
                                </span>
                            </div>
                            <button
                                type="button"
                                class="inline-flex size-7 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-paper-2 hover:text-neg"
                                :disabled="isCancellingInvitation === invitation.uuid"
                                :title="$t('tallies.share.cancel_invitation')"
                                @click="cancelInvitation(invitation.uuid)"
                            >
                                <Loader2 v-if="isCancellingInvitation === invitation.uuid" class="size-3.5 animate-spin" />
                                <X v-else class="size-3.5" />
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <h6 class="eyebrow mb-3">{{ $t('tallies.share.collaborators') }}</h6>
                    <div v-if="isLoading" class="space-y-3">
                        <div
                            v-for="i in 3"
                            :key="i"
                            class="flex items-center justify-between gap-3 border-t border-rule-2 py-3.5 last:border-b last:border-rule-2"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <Skeleton class="size-9 shrink-0 rounded-full" />
                                <div class="min-w-0 space-y-2">
                                    <Skeleton class="h-3 w-24" />
                                    <Skeleton class="h-2 w-16" />
                                </div>
                            </div>
                            <Skeleton class="h-7 w-20 rounded-sm" />
                        </div>
                    </div>
                    <div v-else-if="collaborators.length === 0" class="border-t border-rule-2 py-4 text-center text-sm text-muted-foreground">
                        {{ $t('tallies.share.no_collaborators') }}
                    </div>
                    <div v-else>
                        <div
                            v-for="collaborator in collaborators"
                            :key="collaborator.id"
                            class="grid grid-cols-[auto_1fr_auto] items-center gap-3.5 border-t border-rule-2 py-3.5 last:border-b last:border-rule-2"
                        >
                            <span
                                class="inline-flex size-9 items-center justify-center rounded-full border border-brand/20 bg-brand-soft font-mono text-[13px] font-medium text-brand"
                            >
                                {{ getInitials(collaborator.name) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-foreground">{{ collaborator.name }}</p>
                                <p class="truncate font-mono text-[12px] tracking-[0.02em] text-muted-foreground">{{ collaborator.email }}</p>
                            </div>
                            <div class="flex items-center">
                                <div class="inline-flex items-center rounded-sm border border-rule px-3 py-1.5">
                                    <input
                                        v-model.number="collaborator.percentage"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        class="tabular w-12 border-0 bg-transparent text-right font-mono text-sm text-foreground outline-none"
                                    />
                                    <span class="font-mono text-[13px] text-muted-foreground">%</span>
                                </div>
                                <button
                                    v-if="collaborator.id !== tally?.owner_id"
                                    type="button"
                                    class="ml-1 inline-flex size-8 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-paper-2 hover:text-neg"
                                    @click="removeCollaborator(collaborator.uuid)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <InputError v-if="errors.shares" :message="errors.shares[0]" />
                </div>
            </div>

            <DialogFooter class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                <span class="font-mono text-[13px] tracking-[0.04em]" :class="isPercentageValid ? 'text-pos' : 'text-neg'">
                    {{ $t('tallies.share.total', { total: totalPercentage }) }}
                </span>
                <div class="flex gap-2">
                    <Button variant="ghost" type="button" @click="isOpen = false">{{ $t('common.cancel') }}</Button>
                    <Button :disabled="!isPercentageValid || isSaving || isLoading" type="button" @click="saveShares">
                        <Loader2 v-if="isSaving" class="size-4 animate-spin" />
                        {{ $t('tallies.share.save_changes') }}
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
