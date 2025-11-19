import { User } from '@/types';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUserStore = defineStore('user', () => {
    const name = ref<string | null>(null);
    const email = ref<string | null>(null);

    function setUser(user: User | null) {
        if (!user) {
            name.value = null;
            email.value = null;

            return;
        }

        name.value = user.name ?? null;
        email.value = user.email ?? null;
    }

    return { name, email, setUser };
});
