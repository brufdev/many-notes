import { readonly, ref } from 'vue';

const isEditMode = ref(localStorage.getItem('isEditMode') !== 'false');

const isEditingMarkdown = ref(localStorage.getItem('isEditingMarkdown') === 'true');

export function useTiptapPreferences() {
    function toggleEditMode(): void {
        isEditMode.value = !isEditMode.value;
        localStorage.setItem('isEditMode', String(isEditMode.value));
    }

    function toggleEditingMarkdown(): void {
        isEditingMarkdown.value = !isEditingMarkdown.value;
        localStorage.setItem('isEditingMarkdown', String(isEditingMarkdown.value));
    }

    return {
        isEditMode: readonly(isEditMode),
        isEditingMarkdown: readonly(isEditingMarkdown),
        toggleEditMode,
        toggleEditingMarkdown,
    };
}
