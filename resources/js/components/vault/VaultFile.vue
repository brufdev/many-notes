<script setup lang="ts">
import VaultNodeController from '@/actions/App/Http/Controllers/VaultNodeController';
import TextError from '@/components/form/TextError.vue';
import VaultFileUpdatingSpinner from '@/components/vault/VaultFileUpdatingSpinner.vue';
import VaultToggleContentWidthButton from '@/components/vault/VaultToggleContentWidthButton.vue';
import { useRequest } from '@/composables/useRequest';
import { useVaultActions } from '@/composables/useVaultActions';
import XMark from '@/icons/XMark.vue';
import { useLayoutStore } from '@/stores/layout';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode } from '@/types/vault';
import { computed, ref, useId, useSlots, watch } from 'vue';

interface VaultFileProps {
    node: VaultNode;
}

interface VaultFileEmits {
    nameUpdated: [name: string];
}

const props = defineProps<VaultFileProps>();
const emit = defineEmits<VaultFileEmits>();

const slots = useSlots();
const layoutStore = useLayoutStore();
const vaultTreeStore = useVaultTreeStore();
const vaultActions = useVaultActions();

const form = useRequest<{ name: string }>({ name: props.node.name });

const name = ref(props.node.name);

const nameError = computed(() => form.errors.name);
const nameErrorId = `file-name-${useId()}-error`;

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

function rename(value: string): void {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    form.clearErrors('name');

    debounceTimer = setTimeout(() => {
        const url = VaultNodeController.update.url({
            vault: props.node.vault_id,
            node: props.node.id,
        });

        layoutStore.setVaultNodeUpdating(true);

        form.name = value;

        form.patch(url, {
            onFailure: () => {
                name.value = props.node.name;
            },
            onSuccess: (response: { data: VaultNode }) => {
                emit('nameUpdated', name.value);
                vaultTreeStore.handleNodeSaved(response.data);
            },
            onFinish: () => {
                layoutStore.setVaultNodeUpdating(false);
            },
        });
    }, 1000);
}

watch(
    () => props.node.name,
    value => {
        name.value = value;
        form.clearErrors('name');
    }
);
</script>

<template>
    <div class="flex h-full w-full flex-col">
        <div class="z-[15] flex flex-col p-4 print:hidden" :class="slots.toolbar ? 'gap-3' : ''">
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between gap-2">
                    <input
                        v-model="name"
                        class="flex flex-grow border-0 bg-transparent p-0 px-1 text-lg font-semibold focus:ring-0 focus:outline-none"
                        type="text"
                        spellcheck="false"
                        autocomplete="off"
                        :aria-invalid="!!nameError"
                        :aria-describedby="nameError ? nameErrorId : undefined"
                        @input="rename(name)"
                    />
                    <div class="flex items-center gap-3">
                        <VaultFileUpdatingSpinner />
                        <VaultToggleContentWidthButton />
                        <button title="Close file" @click="vaultActions.closeFile">
                            <XMark class="h-5 w-5" />
                        </button>
                    </div>
                </div>
                <TextError v-if="nameError" :id="nameErrorId" class="px-1" :text="nameError" />
            </div>
            <slot name="toolbar" />
        </div>
        <div class="mb-4 flex w-full flex-grow overflow-y-auto">
            <slot />
        </div>
    </div>
</template>
