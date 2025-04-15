<div class="flex flex-col h-dvh">
    <x-layouts.guestMain>
        <div class="text-center">
            <x-form.sessionStatus :status="session('status')" />
        </div>

        <div class="text-center">
            <x-form.text>
                {{ __('Can\'t sign in? Enter your email and we\'ll send you a link to reset your password.') }}
            </x-form.text>
        </div>

        <x-form wire:submit="send" class="flex flex-col gap-4">
            <x-form.input name="form.email" label="{{ __('Email') }}" type="email" required autofocus />

            <x-form.submit label="{{ __('Send') }}" target="send" />
        </x-form>

        <div class="text-center">
            <x-form.text>
                <x-form.link href="{{ route('login') }}">
                    {{ __('Back to Sign in') }}
                </x-form.link>
            </x-form.text>
        </div>
    </x-layouts.guestMain>
</div>
