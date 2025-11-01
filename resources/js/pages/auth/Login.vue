<script setup lang="ts">
import LoginController from '@/actions/App/Http/Controllers/Auth/LoginController';
import Checkbox from '@/components/form/Checkbox.vue';
import Input from '@/components/form/Input.vue';
import LinkButton from '@/components/form/LinkButton.vue';
import Submit from '@/components/form/Submit.vue';
import TextError from '@/components/form/TextError.vue';
import TextLink from '@/components/form/TextLink.vue';
import TextSuccess from '@/components/form/TextSuccess.vue';
import ArrowDownTray from '@/icons/ArrowDownTray.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { register } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    error?: string;
    providers: object;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status || error" class="text-center">
            <TextSuccess v-if="status" :text="status" />
            <TextError v-if="error" :text="error" />
        </div>

        <div
            v-if="Object.values(providers).length > 0"
            class="flex justify-center gap-2 text-sm font-semibold"
        >
            <div
                v-for="provider in Object.values(providers)"
                :key="'provider-' + provider"
                class="w-1/2"
            >
                <LinkButton :href="'/oauth/' + provider.value" full>
                    <ArrowDownTray class="h-5 w-5 rotate-270" />
                    {{ provider.name }}
                </LinkButton>
            </div>
        </div>

        <div v-if="Object.values(providers).length > 0" class="relative flex items-center">
            <div class="border-light-base-300 dark:border-base-500 flex-grow border-t"></div>
            <span class="mx-4 flex-shrink">Or continue with</span>
            <div class="border-light-base-300 dark:border-base-500 flex-grow border-t"></div>
        </div>

        <Form
            v-slot="{ errors, processing }"
            v-bind="LoginController.store.form()"
            :reset-on-success="['password']"
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
            <Input
                name="password"
                type="password"
                label="Password"
                :error="errors.password"
                required
            />
            <Checkbox name="remember" label="Remember me" />
            <Submit label="Sign in" :processing="processing" />
        </Form>

        <div class="flex flex-col gap-2 text-center text-sm">
            <p v-if="canResetPassword">
                <TextLink href="forgot-password" label="Forgot your password?" />
            </p>

            <p v-if="canRegister">
                Don't have an account?
                <TextLink :href="register().url" label="Sign up" />
            </p>
        </div>
    </GuestLayout>
</template>
