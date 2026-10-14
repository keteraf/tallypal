<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import ResponsiveModal from '@/components/ResponsiveModal.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { NumberStepper } from '@/components/ui/number-stepper';
import { SegmentedControl } from '@/components/ui/segmented-control';
import { Skeleton } from '@/components/ui/skeleton';
import { SplitBar } from '@/components/ui/split-bar';
import { SplitLockButton } from '@/components/ui/split-lock-button';
import { categoryColorFromId } from '@/lib/category';
import apiTallies from '@/routes/api/tallies';
import { Loader2, Trash2, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { I18nT, useI18n } from 'vue-i18n';

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

type SplitMode = 'equal' | 'custom';

const splitMode = ref<SplitMode>('equal');
const lockedIds = ref<number[]>([]);
const splitVersion = ref(0);

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
            normalizeSplit();
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

const clampPercentage = (value: number): number => Math.max(0, Math.min(100, Math.round(Number(value) || 0)));

const equalShares = (count: number): number[] => {
    const base = Math.floor(100 / count);
    const remainder = 100 - base * count;

    return Array.from({ length: count }, (_, index) => base + (index < remainder ? 1 : 0));
};

const applyShares = (shares: number[]) => {
    collaborators.value.forEach((collaborator, index) => {
        collaborator.percentage = shares[index];
    });
    splitVersion.value++;
};

/** Rounds loaded shares to whole percentages and picks the mode they already describe. */
const normalizeSplit = () => {
    lockedIds.value = [];

    const count = collaborators.value.length;
    if (count === 0) {
        return;
    }

    const rounded = collaborators.value.map((c) => clampPercentage(c.percentage));
    rounded[count - 1] += 100 - rounded.reduce((sum, value) => sum + value, 0);

    const equal = equalShares(count);
    splitMode.value = rounded.every((value, index) => value === equal[index]) ? 'equal' : 'custom';
    applyShares(rounded.map((value) => Math.max(0, value)));
};

const setSplitMode = (mode: SplitMode) => {
    splitMode.value = mode;

    if (mode === 'equal') {
        lockedIds.value = [];
        applyShares(equalShares(collaborators.value.length));
    }
};

const toggleLock = (collaboratorId: number, locked: boolean) => {
    lockedIds.value = locked ? [...lockedIds.value, collaboratorId] : lockedIds.value.filter((id) => id !== collaboratorId);
};

/** Sets one share and spreads the remainder over the other unlocked rows, keeping the total at 100%. */
const rebalance = (index: number, requested: number) => {
    const shares = collaborators.value.map((c) => clampPercentage(c.percentage));
    const isLocked = collaborators.value.map((c, i) => i !== index && lockedIds.value.includes(c.id));
    const freeIndexes = shares.map((_, i) => i).filter((i) => i !== index && !isLocked[i]);
    const lockedSum = shares.reduce((sum, value, i) => sum + (isLocked[i] ? value : 0), 0);

    shares[index] = Math.min(clampPercentage(requested), 100 - lockedSum);
    const rest = 100 - lockedSum - shares[index];

    if (freeIndexes.length > 0) {
        const currentSum = freeIndexes.reduce((sum, i) => sum + shares[i], 0);
        freeIndexes.forEach((i) => {
            shares[i] = currentSum > 0 ? Math.round((shares[i] / currentSum) * rest) : Math.round(rest / freeIndexes.length);
        });
        shares[freeIndexes[freeIndexes.length - 1]] += 100 - shares.reduce((sum, value) => sum + value, 0);
    }

    applyShares(shares.map((value) => Math.max(0, value)));
};

const splitSegments = computed(() =>
    collaborators.value.map((collaborator) => ({
        value: Number(collaborator.percentage) || 0,
        color: categoryColorFromId(collaborator.id),
    })),
);

const splitModeOptions = computed(() => [
    { value: 'equal' as SplitMode, label: t('tallies.share.mode_equal') },
    { value: 'custom' as SplitMode, label: t('tallies.share.mode_custom') },
]);

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

const getInitial = (name: string): string => name.trim().charAt(0).toUpperCase();
</script>

<template>
    <ResponsiveModal v-model:open="isOpen" :title="$t('tallies.share.title')" :description="$t('tallies.share.description')">
        <I18nT keypath="tallies.share.intro" tag="p" class="mb-5 text-[13.5px] leading-normal text-text-3">
            <template #name>
                <b class="font-semibold text-text-2">{{ tally?.name }}</b>
            </template>
        </I18nT>

        <FormField :label="$t('tallies.share.invite_by_email')" for="email" :error="errors.email?.[0]">
            <div class="flex gap-2">
                <Input
                    id="email"
                    v-model="newCollaboratorEmail"
                    variant="filled"
                    type="email"
                    :placeholder="$t('common.placeholder.email')"
                    :disabled="isInviting"
                    class="min-w-0 flex-1 font-mono text-[12.5px] tracking-[-0.01em]"
                    @keyup.enter="addCollaborator"
                />
                <Button type="button" size="sm" class="h-auto shrink-0" :disabled="isInviting || !newCollaboratorEmail" @click="addCollaborator">
                    <Loader2 v-if="isInviting" class="size-4 animate-spin" />
                    {{ $t('tallies.share.send') }}
                </Button>
            </div>
            <p v-if="successMessage" class="mt-1.5 text-[12px] text-pos">{{ successMessage }}</p>
        </FormField>

        <FormField :label="$t('tallies.share.members')">
            <div v-if="isLoading">
                <div v-for="i in 3" :key="i" class="flex items-center gap-[11px] border-t border-line py-[11px] first:border-t-0">
                    <Skeleton class="size-[26px] shrink-0 rounded-full" />
                    <div class="min-w-0 flex-1 space-y-2">
                        <Skeleton class="h-3 w-24" />
                        <Skeleton class="h-2 w-16" />
                    </div>
                </div>
            </div>
            <div v-else>
                <div
                    v-for="collaborator in collaborators"
                    :key="collaborator.id"
                    class="flex items-center gap-[11px] border-t border-line py-[11px] first:border-t-0"
                >
                    <Avatar size="sm">
                        <AvatarFallback :color="categoryColorFromId(collaborator.id)">{{ getInitial(collaborator.name) }}</AvatarFallback>
                    </Avatar>
                    <span class="min-w-0 flex-1">
                        <div class="truncate text-[14px] font-medium">{{ collaborator.name }}</div>
                        <div class="mt-px truncate text-[11.5px] text-text-3">{{ collaborator.email }}</div>
                    </span>
                    <Badge v-if="collaborator.id === tally?.owner_id" variant="pill">{{ $t('tallies.share.owner') }}</Badge>
                    <button
                        v-else
                        type="button"
                        class="grid size-8 shrink-0 place-items-center rounded-full text-text-3 transition-colors hover:bg-surface-2 hover:text-neg"
                        :aria-label="$t('tallies.share.remove_member')"
                        :title="$t('tallies.share.remove_member')"
                        @click="removeCollaborator(collaborator.uuid)"
                    >
                        <Trash2 class="size-4" />
                    </button>
                </div>
                <div
                    v-for="invitation in pendingInvitations"
                    :key="invitation.uuid"
                    class="flex items-center gap-[11px] border-t border-line py-[11px] first:border-t-0"
                >
                    <Avatar size="sm">
                        <AvatarFallback>{{ getInitial(invitation.email) }}</AvatarFallback>
                    </Avatar>
                    <span class="min-w-0 flex-1">
                        <div class="truncate text-[14px] font-medium">{{ invitation.email }}</div>
                        <div class="mt-px text-[11.5px] text-text-3">{{ $t('tallies.share.invited_not_joined') }}</div>
                    </span>
                    <button
                        type="button"
                        class="grid size-8 shrink-0 place-items-center rounded-full text-text-3 transition-colors hover:bg-surface-2 hover:text-neg"
                        :disabled="isCancellingInvitation === invitation.uuid"
                        :aria-label="$t('tallies.share.cancel_invitation')"
                        :title="$t('tallies.share.cancel_invitation')"
                        @click="cancelInvitation(invitation.uuid)"
                    >
                        <Loader2 v-if="isCancellingInvitation === invitation.uuid" class="size-3.5 animate-spin" />
                        <X v-else class="size-4" />
                    </button>
                </div>
            </div>
            <InputError v-if="errors.general" :message="errors.general[0]" class="mt-1.5" />
        </FormField>

        <FormField :label="$t('tallies.share.split')">
            <div class="rounded-sm bg-surface-2 px-3.5 py-3">
                <div class="mb-3 flex items-center justify-between gap-2.5">
                    <SegmentedControl
                        variant="mobile"
                        size="sm"
                        :model-value="splitMode"
                        :options="splitModeOptions"
                        @update:model-value="setSplitMode"
                    />
                    <span class="text-[12px] text-text-3">
                        {{ $t('tallies.share.total') }}
                        <span class="font-mono font-semibold" :class="isPercentageValid ? 'text-pos' : 'text-neg'">{{ totalPercentage }}%</span>
                    </span>
                </div>
                <SplitBar :segments="splitSegments" />
                <div v-if="isLoading" class="space-y-3 py-2">
                    <Skeleton v-for="i in 2" :key="i" class="h-7 w-full rounded-full" />
                </div>
                <template v-else>
                    <div
                        v-for="(collaborator, index) in collaborators"
                        :key="`${collaborator.id}-${splitVersion}`"
                        class="flex items-center gap-2.5 border-t border-line py-2.5 first:border-t-0"
                    >
                        <Avatar size="sm">
                            <AvatarFallback :color="categoryColorFromId(collaborator.id)">{{ getInitial(collaborator.name) }}</AvatarFallback>
                        </Avatar>
                        <span class="min-w-0 flex-1">
                            <div class="truncate text-[13.5px] font-medium tracking-[-0.01em]">{{ collaborator.name }}</div>
                            <div class="mt-px text-[11.5px] text-text-3">
                                {{ collaborator.id === tally?.owner_id ? $t('tallies.share.owner') : collaborator.email }}
                            </div>
                        </span>
                        <NumberStepper
                            size="sm"
                            suffix="%"
                            :step="5"
                            :label="$t('tallies.share.member_share', { name: collaborator.name })"
                            :model-value="collaborator.percentage"
                            :class="splitMode === 'equal' ? 'pointer-events-none opacity-45' : ''"
                            @update:model-value="rebalance(index, $event)"
                        />
                        <SplitLockButton
                            :label="$t('tallies.share.pin_share')"
                            :locked="lockedIds.includes(collaborator.id)"
                            :class="splitMode === 'equal' ? 'invisible' : ''"
                            @update:locked="toggleLock(collaborator.id, $event)"
                        />
                    </div>
                </template>
                <p class="mt-2.5 text-[11.5px] leading-[1.45] text-text-3">
                    {{ splitMode === 'equal' ? $t('tallies.share.hint_equal') : $t('tallies.share.hint_custom') }}
                </p>
            </div>
            <InputError v-if="errors.shares" :message="errors.shares[0]" class="mt-1.5" />
        </FormField>

        <div class="mt-1.5 flex gap-2.5">
            <Button
                type="button"
                variant="ghost"
                size="touch"
                class="hidden flex-1 bg-surface-2 text-text hover:bg-surface-3 md:inline-flex"
                @click="isOpen = false"
            >
                {{ $t('common.cancel') }}
            </Button>
            <Button type="button" size="touch" class="flex-1" :disabled="!isPercentageValid || isSaving || isLoading" @click="saveShares">
                <Loader2 v-if="isSaving" class="size-4 animate-spin" />
                {{ $t('tallies.share.done') }}
            </Button>
        </div>
    </ResponsiveModal>
</template>
