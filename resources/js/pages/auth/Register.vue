<script setup lang="ts">
import RegisterController from '@/actions/App/Http/Controllers/Auth/RegisterController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import TextLink from '@/components/form/TextLink.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <Form
            v-slot="{ errors, processing }"
            v-bind="RegisterController.store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            class="flex flex-col gap-4"
            autocomplete="off"
            novalidate
        >
            <Input name="name" type="text" label="Name" :error="errors.name" required autofocus />
            <Input name="email" type="email" label="Email" :error="errors.email" required />
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
            <Submit label="Register" :processing="processing" />
        </Form>

        <div class="flex flex-col gap-2 text-center text-sm">
            <p>
                Already registered?
                <TextLink :href="login().url" label="Sign in" />
            </p>
        </div>
    </GuestLayout>
</template>
