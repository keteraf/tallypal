<script setup lang="ts">
import ResponsiveModal from '@/components/ResponsiveModal.vue';
import AddTransactionForm from '@/components/tallies/AddTransactionForm.vue';
import NewTallyForm from '@/components/tallies/NewTallyForm.vue';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { useAddTransaction } from '@/composables/useAddTransaction';
import { useNewTally } from '@/composables/useNewTally';
import tallies from '@/routes/tallies';
import { useHttp } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * App-wide overlay host, mounted once in the responsive shell. Binds the
 * Add-transaction / New-tally openers to their viewport-aware modals.
 *
 * Add-tx flow:
 * 1. Opens → fetches `tallies.picker` (once per open).
 * 2. 0 tallies → empty state directing user to New-tally.
 * 3. Otherwise the first tally is auto-selected and its form-data fetched (skeleton, then AddTransactionForm).
 * 4. The form's inline Tally select refetches form-data in place, keeping the typed values.
 * 5. State is reset when the overlay closes.
 */
const { t } = useI18n();

const { isOpen: addTransactionOpen } = useAddTransaction();
const { isOpen: newTallyOpen } = useNewTally();

interface TallyPickerItem {
    tally_id: number;
    uuid: string;
    name: string;
    color: string | null;
    glyph: string | null;
}

interface FormDataPayload {
    categories: Array<{ category_id: number; name: string }>;
    currencies: Array<{ currency_id: number; uuid: string; name: string; code: string; symbol: string }>;
    parties: Array<{ id: number; name: string }>;
    default_currency_uuid: string | null;
}

const pickerTallies = ref<TallyPickerItem[] | null>(null);
const selectedTallyUuid = ref<string | null>(null);
const formData = ref<FormDataPayload | null>(null);
const pickerLoading = ref(false);
const formDataLoading = ref(false);

const pickerHttp = useHttp<Record<string, never>, TallyPickerItem[]>({});
const formDataHttp = useHttp<Record<string, never>, FormDataPayload>({});


const modalTitle = computed(() => {
    if (pickerTallies.value === null || pickerLoading.value) {
        return t('navigation.add_transaction_title');
    }
    if (pickerTallies.value.length === 0) {
        return t('navigation.no_tallies_title');
    }
    return t('navigation.add_transaction_title');
});

function resetState(): void {
    pickerTallies.value = null;
    selectedTallyUuid.value = null;
    formData.value = null;
    pickerLoading.value = false;
    formDataLoading.value = false;
}

function selectTally(uuid: string): void {
    selectedTallyUuid.value = uuid;
    formDataLoading.value = formData.value === null;

    formDataHttp.get(tallies.transactions.formData.url(uuid), {
        onSuccess: (response) => {
            formData.value = response;
            formDataLoading.value = false;
        },
        onError: () => {
            formDataLoading.value = false;
        },
    });
}

function loadPicker(): void {
    if (pickerTallies.value !== null) {
        return;
    }
    pickerLoading.value = true;

    pickerHttp.get(tallies.picker.url(), {
        onSuccess: (response) => {
            pickerTallies.value = response;
            pickerLoading.value = false;

            if (response.length > 0) {
                selectTally(response[0].uuid);
            }
        },
        onError: () => {
            pickerLoading.value = false;
        },
    });
}

watch(addTransactionOpen, (isOpen) => {
    if (isOpen) {
        loadPicker();
    } else {
        resetState();
    }
});

function handleSubmitted(): void {
    addTransactionOpen.value = false;
}
</script>

<template>
    <ResponsiveModal v-model:open="addTransactionOpen" :title="modalTitle">
        <!-- Loading skeleton while fetching tally list -->
        <template v-if="pickerLoading">
            <div class="space-y-3 py-4">
                <Skeleton class="h-12 w-full rounded-card" />
                <Skeleton class="h-12 w-full rounded-card" />
                <Skeleton class="h-12 w-4/5 rounded-card" />
            </div>
        </template>

        <!-- Empty state: no tallies -->
        <template v-else-if="pickerTallies !== null && pickerTallies.length === 0">
            <div class="py-8 text-center">
                <p class="text-sm text-text-3">{{ t('navigation.no_tallies_description') }}</p>
                <Button
                    class="mt-4"
                    size="touch"
                    @click="
                        addTransactionOpen = false;
                        newTallyOpen = true;
                    "
                >
                    {{ t('tallies.new_tally') }}
                </Button>
            </div>
        </template>

        <!-- Form data loading skeleton -->
        <template v-else-if="selectedTallyUuid !== null && formData === null">
            <div class="space-y-3 py-4">
                <Skeleton class="h-16 w-full rounded-card" />
                <Skeleton class="h-10 w-full rounded-card" />
                <Skeleton class="h-10 w-full rounded-card" />
                <Skeleton class="h-10 w-3/4 rounded-card" />
            </div>
        </template>

        <!-- The real add-transaction form -->
        <template v-else-if="selectedTallyUuid !== null && formData !== null">
            <AddTransactionForm
                :tally-uuid="selectedTallyUuid"
                :categories="formData.categories"
                :currencies="formData.currencies"
                :parties="formData.parties"
                :default-currency-uuid="formData.default_currency_uuid"
                :tally-options="pickerTallies ?? []"
                @change-tally="selectTally"
                @submitted="handleSubmitted"
                @cancel="addTransactionOpen = false"
            />
        </template>
    </ResponsiveModal>

    <ResponsiveModal v-model:open="newTallyOpen" :title="t('tallies.new_tally')">
        <NewTallyForm @submitted="newTallyOpen = false" />
    </ResponsiveModal>
</template>
