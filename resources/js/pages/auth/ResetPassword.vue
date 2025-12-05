<script setup lang="ts">
import ResetPasswordController from '@/actions/App/Http/Controllers/Auth/ResetPasswordController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import TextLink from '@/components/form/TextLink.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    token: string;
}>();
</script>

<template>
    <GuestLayout>
        <Head title="Reset password" />

        <Form
            v-slot="{ errors, processing }"
            v-bind="ResetPasswordController.store.form({ token })"
            class="flex flex-col gap-6 inert:pointer-events-none"
            autocomplete="off"
            novalidate
            disable-while-processing
        >
            <Input
                name="email"
                type="email"
                label="Email"
                :error="errors.email"
                required
                autofocus
            />
            <Input
                name="password"
                type="password"
                label="Password"
                :error="errors.password"
                required
            />
            <Input
                name="password_confirmation"
                type="password"
                label="Confirm password"
                :error="errors.password_confirmation"
                required
            />
            <Submit label="Send" full-width :processing="processing" />
        </Form>

        <div class="text-center text-sm">
            <TextLink :href="login().url" label="Back to Sign in" />
        </div>
    </GuestLayout>
</template>
