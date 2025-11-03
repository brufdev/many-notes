<script setup lang="ts">
import ForgotPasswordController from '@/actions/App/Http/Controllers/Auth/ForgotPasswordController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import TextLink from '@/components/form/TextLink.vue';
import TextSuccess from '@/components/form/TextSuccess.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <GuestLayout>
        <Head title="Forgot password" />

        <div v-if="status" class="text-center">
            <TextSuccess :text="status" />
        </div>

        <div class="flex flex-col gap-2 text-center text-sm">
            Can't sign in? Enter your email and we'll send you a link to reset your password.
        </div>

        <Form
            v-slot="{ errors, processing }"
            v-bind="ForgotPasswordController.store.form()"
            :reset-on-success="['email']"
            class="flex flex-col gap-6"
            autocomplete="off"
            novalidate
        >
            <Input
                name="email"
                type="email"
                label="Email"
                :error="errors.email"
                required
                autofocus
            />
            <Submit label="Send" :processing="processing" />
        </Form>

        <div class="text-center text-sm">
            <TextLink :href="login().url" label="Back to Sign in" />
        </div>
    </GuestLayout>
</template>
