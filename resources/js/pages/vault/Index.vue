<script setup lang="ts">
import VaultController from '@/actions/App/Http/Controllers/VaultController';
import Menu from '@/components/menu/Menu.vue';
import MenuItem from '@/components/menu/MenuItem.vue';
import NotificationMenu from '@/components/menu/NotificationMenu.vue';
import UserMenu from '@/components/menu/UserMenu.vue';
import ConfirmationModal from '@/components/modal/ConfirmationModal.vue';
import VaultCreateModal from '@/components/modal/VaultCreateModal.vue';
import VaultEditModal from '@/components/modal/VaultEditModal.vue';
import VaultImportModal from '@/components/modal/VaultImportModal.vue';
import { useDownload } from '@/composables/useDownload';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import ArrowDownTray from '@/icons/ArrowDownTray.vue';
import ArrowUpTray from '@/icons/ArrowUpTray.vue';
import EllipsisVertical from '@/icons/EllipsisVertical.vue';
import PencilSquare from '@/icons/PencilSquare.vue';
import Plus from '@/icons/Plus.vue';
import Trash from '@/icons/Trash.vue';
import UserGroup from '@/icons/UserGroup.vue';
import { reloadWithLoading } from '@/inertia/reloadWithLoading';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { exportMethod } from '@/routes/vaults';
import { AppPageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { computed, watch } from 'vue';

interface Vault {
    id: number;
    name: string;
    accepted_collaborators_count: number;
    created_by: number;
    updated_at: string;
}

defineProps<{
    visibleVaults: Vault[];
}>();

const page = usePage<AppPageProps>();
const { openModal } = useModalManager();
const { createToast } = useToast();
const { error, download } = useDownload();

const userId = computed(() => page.props.app?.user?.id);

watch(error, message => {
    if (typeof message === 'string' && message.trim().length > 0) {
        createToast(message, 'error');
    }
});

const formatDate = (date: string): string => {
    return new Date(date).toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

const openDeleteVaultConfirmation = (vaultId: number) => {
    openModal(ConfirmationModal, {
        title: 'Delete vault',
        routeForm: VaultController.destroy.form({ vault: vaultId }),
        content: 'Are you sure you want to delete this vault?',
        successMessage: 'Vault deleted',
    });
};

useEcho(`User.${userId.value}`, 'VaultListUpdatedEvent', () => {
    reloadWithLoading({ only: ['visibleVaults'] });
});
</script>

<template>
    <AuthLayout>
        <Head title="Vaults" />

        <template #header>
            <div class="flex items-center gap-3"></div>

            <div class="flex items-center gap-3">
                <NotificationMenu />

                <UserMenu />
            </div>
        </template>

        <div class="flex flex-1 flex-col">
            <div class="flex items-center justify-between p-4">
                <div class="text-lg font-semibold">My vaults</div>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        title="Import vault"
                        @click="
                            openModal(VaultImportModal, {
                                title: 'Import vault',
                            })
                        "
                    >
                        <ArrowUpTray class="h-5 w-5" />
                    </button>

                    <button
                        type="button"
                        title="Create vault"
                        @click="
                            openModal(VaultCreateModal, {
                                title: 'Create vault',
                            })
                        "
                    >
                        <Plus class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div class="mb-4 flex flex-grow flex-col overflow-y-auto px-4">
                <div class="flex flex-col">
                    <template v-if="visibleVaults">
                        <div
                            v-for="vault in visibleVaults"
                            :key="vault.id"
                            class="border-light-base-300 dark:border-base-500 items-center border-b pt-3 pb-4 last:border-b-0"
                        >
                            <div class="flex w-full items-center justify-between">
                                <Link
                                    class="hover:text-primary-600 dark:hover:text-primary-300 flex flex-grow flex-col gap-2"
                                    :href="'/vaults/' + vault.id"
                                    :title="vault.name"
                                >
                                    <span
                                        class="flex-grow overflow-hidden text-ellipsis whitespace-nowrap"
                                    >
                                        {{ vault.name }}
                                    </span>
                                    <span
                                        class="text-light-base-700 dark:text-base-200 overflow-hidden text-xs text-ellipsis whitespace-nowrap"
                                    >
                                        Updated on {{ formatDate(vault.updated_at) }}
                                    </span>
                                </Link>
                                <div class="flex items-center justify-center gap-2">
                                    <span
                                        v-if="vault.accepted_collaborators_count > 0"
                                        title="This vault has collaborators"
                                    >
                                        <UserGroup class="h-[1.1rem] w-[1.1rem]" />
                                    </span>

                                    <Menu type="dropdown">
                                        <template #trigger>
                                            <EllipsisVertical class="h-5 w-5" />
                                        </template>

                                        <template #default="{ closeMenu }">
                                            <div class="min-w-[10rem]">
                                                <MenuItem
                                                    label="Edit"
                                                    :icon="PencilSquare"
                                                    @click="
                                                        closeMenu();
                                                        openModal(VaultEditModal, {
                                                            title: 'Edit vault',
                                                            id: vault.id,
                                                            name: vault.name,
                                                        });
                                                    "
                                                />
                                                <MenuItem
                                                    label="Export"
                                                    :icon="ArrowDownTray"
                                                    @click="
                                                        closeMenu();
                                                        download(
                                                            exportMethod.url({
                                                                vault: vault.id,
                                                            })
                                                        );
                                                    "
                                                />
                                                <MenuItem
                                                    v-if="vault.created_by === userId"
                                                    label="Delete"
                                                    :icon="Trash"
                                                    @click="
                                                        closeMenu();
                                                        openDeleteVaultConfirmation(vault.id);
                                                    "
                                                />
                                            </div>
                                        </template>
                                    </Menu>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-else class="items-center pt-3 pb-4">
                        <p>You have no vaults yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
