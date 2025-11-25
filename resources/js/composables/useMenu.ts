import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

interface Position {
    top: number;
    left: number;
}

export function useMenu() {
    const isOpen = ref(false);
    const position = ref<Position>({ top: 0, left: 0 });
    const menuRef = ref<HTMLElement | null>(null);
    const triggerRef = ref<HTMLElement | null>(null);

    function openMenu() {
        isOpen.value = true;
        nextTick(adjustMenuPosition);
    }

    function closeMenu() {
        isOpen.value = false;
    }

    function toggleMenu() {
        if (isOpen.value) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    function adjustMenuPosition() {
        const menu = menuRef.value;
        const trigger = triggerRef.value;

        if (!menu || !trigger) {
            return;
        }

        const menuRect = menu.getBoundingClientRect();
        const triggerRect = trigger.getBoundingClientRect();

        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        const spaceBelow = viewportHeight - triggerRect.bottom;
        const spaceAbove = triggerRect.top;

        let top: number;

        if (spaceBelow >= menuRect.height) {
            top = triggerRect.bottom;
        } else if (spaceAbove >= menuRect.height) {
            top = triggerRect.top - menuRect.height;
        } else {
            top = spaceBelow >= spaceAbove ? triggerRect.bottom : triggerRect.top - menuRect.height;
        }

        let left = triggerRect.left + triggerRect.width / 2 - menuRect.width / 2;

        if (left < 0) {
            left = 0;
        }

        if (left + menuRect.width > viewportWidth) {
            left = viewportWidth - menuRect.width;
        }

        position.value = { top, left };
    }

    const handleClickOutside = (e: MouseEvent) => {
        const menu = menuRef.value;
        const trigger = triggerRef.value;

        if (!menu || !trigger) {
            return;
        }

        const target = e.target as Node;

        if (menu.contains(target) || trigger.contains(target)) {
            return;
        }

        closeMenu();
    };

    onMounted(() => {
        document.addEventListener('click', handleClickOutside, true);
        window.addEventListener('resize', adjustMenuPosition);
    });

    onBeforeUnmount(() => {
        document.removeEventListener('click', handleClickOutside, true);
        window.removeEventListener('resize', adjustMenuPosition);
    });

    return {
        triggerRef,
        menuRef,
        isOpen,
        position,
        openMenu,
        closeMenu,
        toggleMenu,
    };
}
