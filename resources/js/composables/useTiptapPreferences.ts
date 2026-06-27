import { readonly, ref } from 'vue';

const isEditMode = ref(localStorage.getItem('isEditMode') !== 'false');

const isEditingMarkdown = ref(localStorage.getItem('isEditingMarkdown') === 'true');

const isSpellcheckEnabled = ref(localStorage.getItem('isSpellcheckEnabled') === 'true');

export function useTiptapPreferences() {
    function toggleEditMode(): void {
        isEditMode.value = !isEditMode.value;
        localStorage.setItem('isEditMode', String(isEditMode.value));
    }

    function toggleEditingMarkdown(): void {
        isEditingMarkdown.value = !isEditingMarkdown.value;
        localStorage.setItem('isEditingMarkdown', String(isEditingMarkdown.value));
    }

    function toggleSpellcheck(): void {
        isSpellcheckEnabled.value = !isSpellcheckEnabled.value;
        localStorage.setItem('isSpellcheckEnabled', String(isSpellcheckEnabled.value));
    }

    return {
        isEditMode: readonly(isEditMode),
        isEditingMarkdown: readonly(isEditingMarkdown),
        isSpellcheckEnabled: readonly(isSpellcheckEnabled),
        toggleEditMode,
        toggleEditingMarkdown,
        toggleSpellcheck,
    };
}
