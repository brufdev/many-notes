<div class="flex flex-col h-dvh">
    <x-layouts.guestMain>
        <x-form wire:submit="send" class="flex flex-col gap-4">
            <x-form.input name="form.email" label="{{ __('Email') }}" type="email" required />

            <x-form.input name="form.password" label="{{ __('New password') }}" type="password" required autofocus />

            <x-form.input name="form.password_confirmation" label="{{ __('Confirm password') }}" type="password"
                required />

            <x-form.submit label="{{ __('Reset Password') }}" target="send" />
        </x-form>

        <div class="text-center">
            <x-form.text>
                <x-form.link wire:navigate href="{{ route('login') }}">
                    {{ __('Back to Sign in') }}
                </x-form.link>
            </x-form.text>
        </div>
    </x-layouts.guestMain>
</div>
