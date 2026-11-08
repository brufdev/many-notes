import VaultNodeImportController from '@/actions/App/Http/Controllers/VaultNodeImportController';
import { useRequest } from '@/composables/useRequest';
import { useToast } from '@/composables/useToast';
import { useVaultTreePreferences } from '@/composables/useVaultTreePreferences';
import { useVaultRecentFileStore } from '@/stores/vaultRecentFile';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode } from '@/types/vault';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface ImportFilesOptions {
    vaultId: number;
    parentId: number | null;
    files: File[];
    onSuccess?: (files: VaultNode[]) => void;
    onFinish?: () => void;
}

export function getUploadableClipboardFiles(event: ClipboardEvent): File[] {
    if (!event.clipboardData) {
        return [];
    }

    const files = Array.from(event.clipboardData.files);
    const types = Array.from(event.clipboardData.types);

    if (files.length === 0 || types.includes('text/html')) {
        return [];
    }

    return files;
}

export function useVaultFileUpload() {
    const vaultRecentFileStore = useVaultRecentFileStore();
    const vaultTreeStore = useVaultTreeStore();
    const page = usePage();
    const { createToast } = useToast();
    const { isShowingUnsupportedFiles } = useVaultTreePreferences();

    const uploadMaxFilesizeBytes = computed(
        () => page.props.app?.metadata?.upload_max_filesize_bytes ?? 0
    );
    const tooLargeMessage = computed(
        () =>
            `Each file must be smaller than ${page.props.app?.metadata?.upload_max_filesize ?? '0'}`
    );

    const form = useRequest<{
        parent_id: number | null;
        files: File[];
    }>({
        parent_id: null,
        files: [],
    });

    const filesError = computed<string | undefined>(() => {
        const errors = form.errors as unknown as Record<string, string | undefined>;
        const key = Object.keys(errors).find(k => k === 'files' || k.startsWith('files.'));

        return key ? errors[key] : undefined;
    });

    function filterValidFiles(files: File[]): File[] {
        return files.filter(file => file.size <= uploadMaxFilesizeBytes.value);
    }

    function importFiles(options: ImportFilesOptions): void {
        const validFiles = filterValidFiles(options.files);

        if (validFiles.length === 0) {
            createToast(tooLargeMessage.value, 'error');
            options.onFinish?.();

            return;
        }

        form.parent_id = options.parentId;
        form.files = validFiles;

        form.post(VaultNodeImportController.url({ vault: options.vaultId }), {
            onSuccess: (response: { files: VaultNode[]; skipped_count: number }) => {
                const hiddenCount = isShowingUnsupportedFiles.value
                    ? 0
                    : response.files.filter(file => file.type === 'other').length;
                const importedCount = response.files.length - hiddenCount;
                const skippedCount = response.skipped_count;
                const skippedMessage = skippedCount > 0 ? `, ${skippedCount} skipped` : '';

                if (importedCount === 0 && hiddenCount === 0) {
                    const message =
                        skippedCount === 1
                            ? 'Hidden or invalid file name'
                            : 'Hidden or invalid file names';
                    createToast(message, 'error');

                    return;
                }

                if (importedCount === 0) {
                    const message =
                        hiddenCount === 1 ? 'file saved, not shown' : 'files saved, not shown';
                    createToast(`${hiddenCount} ${message}${skippedMessage}`, 'info');
                } else {
                    const message = importedCount === 1 ? 'file imported' : 'files imported';
                    const hiddenMessage = hiddenCount > 0 ? `, ${hiddenCount} not shown` : '';
                    createToast(
                        `${importedCount} ${message}${hiddenMessage}${skippedMessage}`,
                        'success'
                    );
                }

                for (const file of response.files) {
                    vaultTreeStore.handleNodeSaved(file);

                    if (file.is_file && file.type !== 'other') {
                        vaultRecentFileStore.upsertRecentFile(file);
                    }
                }

                options.onSuccess?.(response.files);
            },
            onFinish: () => options.onFinish?.(),
        });
    }

    return { form, filesError, tooLargeMessage, filterValidFiles, importFiles };
}
