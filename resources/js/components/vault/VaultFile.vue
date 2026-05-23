<script setup lang="ts">
import VaultNodeController from '@/actions/App/Http/Controllers/VaultNodeController';
import VaultFileUpdatingSpinner from '@/components/vault/VaultFileUpdatingSpinner.vue';
import VaultToggleContentWidthButton from '@/components/vault/VaultToggleContentWidthButton.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useToast } from '@/composables/useToast';
import { useVaultActions } from '@/composables/useVaultActions';
import XMark from '@/icons/XMark.vue';
import { useLayoutStore } from '@/stores/layout';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode } from '@/types/vault';
import { ref, useSlots, watch } from 'vue';

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
const form = useAxiosForm({});
const { createToast } = useToast();

const fileName = ref(props.node.name);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

function rename(name: string): void {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        const url = VaultNodeController.update.url({
            vault: props.node.vault_id,
            node: props.node.id,
        });

        layoutStore.setVaultNodeUpdating(true);

        form.send({
            url: url,
            method: 'patch',
            data: {
                name: name,
            },
            onError: error => {
                const message = error.response?.statusText ?? 'Something went wrong';
                createToast(message, 'error');
                fileName.value = props.node.name;
            },
            onSuccess: (response: { data: VaultNode }) => {
                emit('nameUpdated', fileName.value);
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
        fileName.value = value;
    }
);
</script>

<template>
    <div class="flex h-full w-full flex-col">
        <div class="z-[15] flex flex-col p-4 print:hidden" :class="slots.toolbar ? 'gap-3' : ''">
            <div class="flex items-center justify-between gap-2">
                <input
                    v-model="fileName"
                    class="flex flex-grow border-0 bg-transparent p-0 px-1 text-lg font-semibold focus:ring-0 focus:outline-none"
                    type="text"
                    spellcheck="false"
                    autocomplete="off"
                    @input="rename(fileName)"
                />
                <div class="flex items-center gap-3">
                    <VaultFileUpdatingSpinner />
                    <VaultToggleContentWidthButton />
                    <button title="Close file" @click="vaultActions.closeFile">
                        <XMark class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <slot name="toolbar" />
        </div>
        <div class="mb-4 flex w-full flex-grow overflow-y-auto">
            <slot />
        </div>
    </div>
</template>
