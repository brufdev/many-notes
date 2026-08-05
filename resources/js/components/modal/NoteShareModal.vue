<script setup lang="ts">
import { destroy, store } from '@/actions/App/Http/Controllers/VaultNodeShareController';
import ModelInput from '@/components/form/ModelInput.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import Trash from '@/icons/Trash.vue';
import { ref } from 'vue';

const { closeModal } = useModalManager();
const { createToast } = useToast();

const props = defineProps<{
    vaultId: number;
    nodeId: number;
    shareUrl: string | null;
    onUpdate: (url: string | null) => void;
}>();

const shareUrl = ref(props.shareUrl);
const isConfirmingRevoke = ref(false);
const isCopied = ref(false);

let copiedTimeout: ReturnType<typeof setTimeout> | null = null;

const form = useAxiosForm({});

const createLink = () => {
    form.send<{ data: { url: string } }>({
        url: store.url({ vault: props.vaultId, node: props.nodeId }),
        method: 'post',
        onError: error => {
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: payload => {
            shareUrl.value = payload.data.url;
            props.onUpdate(shareUrl.value);
        },
    });
};

const copyLink = async () => {
    if (!shareUrl.value) {
        return;
    }

    await navigator.clipboard.writeText(shareUrl.value);

    isCopied.value = true;

    if (copiedTimeout) {
        clearTimeout(copiedTimeout);
    }

    copiedTimeout = setTimeout(() => {
        isCopied.value = false;
    }, 2000);
};

const confirmRevoke = () => {
    form.send({
        url: destroy.url({ vault: props.vaultId, node: props.nodeId }),
        method: 'delete',
        onError: error => {
            isConfirmingRevoke.value = false;
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: () => {
            shareUrl.value = null;
            props.onUpdate(null);
            closeModal();
        },
    });
};
</script>

<template>
    <div class="flex flex-col gap-6">
        <template v-if="isConfirmingRevoke">
            <p class="text-sm opacity-75">
                Are you sure you want to revoke this share link? Anyone using it will no longer be
                able to view this note.
            </p>
            <div class="flex justify-end gap-2 py-1">
                <SecondaryButton :disabled="form.processing" @click="isConfirmingRevoke = false">
                    Cancel
                </SecondaryButton>
                <Submit label="Revoke link" :processing="form.processing" @click="confirmRevoke" />
            </div>
        </template>
        <template v-else-if="shareUrl">
            <p class="text-sm opacity-75">
                Anyone with this link can view a read-only version of this note. It stays up to date
                with your edits until you revoke it.
            </p>
            <div class="flex items-end gap-2">
                <ModelInput
                    class="grow"
                    name="share_url"
                    type="text"
                    :model-value="shareUrl"
                    readonly
                    @focus="($event.target as HTMLInputElement).select()"
                />
                <SecondaryButton @click="copyLink">{{
                    isCopied ? 'Copied!' : 'Copy'
                }}</SecondaryButton>
            </div>
            <div class="flex justify-end">
                <button
                    type="button"
                    class="text-error-500 flex items-center gap-1 text-sm font-semibold"
                    @click="isConfirmingRevoke = true"
                >
                    <Trash class="h-4 w-4" />
                    Revoke link
                </button>
            </div>
        </template>
        <template v-else>
            <p class="text-sm opacity-75">
                Create a public, read-only link to this note. Anyone with the link can view it
                without an account.
            </p>
            <form
                class="flex flex-col gap-6 inert:pointer-events-none"
                :inert="form.processing"
                @submit.prevent="createLink"
            >
                <div class="flex justify-end gap-2 py-1">
                    <Submit label="Create link" :processing="form.processing" />
                </div>
            </form>
        </template>
    </div>
</template>
