<script setup lang="ts">
import LoginController from '@/actions/App/Http/Controllers/Auth/LoginController';
import {
    Checkbox,
    Input,
    LinkButton,
    Submit,
    TextError,
    TextLink,
    TextSuccess,
} from '@/components/form';
import { ArrowDownTray } from '@/icons';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { register } from '@/routes';
import { Form, Head, usePage } from '@inertiajs/vue3';

defineOptions({ layout: GuestLayout });

defineProps<{
    providers: object;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const page = usePage();
</script>

<template>
    <Head title="Log in" />

    <div v-if="page.flash.status || page.flash.error" class="text-center">
        <TextSuccess v-if="page.flash.status" :text="page.flash.status" />
        <TextError v-if="page.flash.error" :text="page.flash.error" />
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
        class="flex flex-col gap-6 inert:pointer-events-none"
        autocomplete="off"
        novalidate
        disable-while-processing
        :reset-on-success="['password']"
    >
        <Input name="email" type="email" label="Email" :error="errors.email" required autofocus />
        <Input name="password" type="password" label="Password" :error="errors.password" required />
        <Checkbox name="remember" label="Remember me" />
        <Submit label="Sign in" full-width :processing="processing" />
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
</template>
