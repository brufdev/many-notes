<div class="flex flex-col h-dvh">
    <x-layouts.guestMain>
        @if (session('status') || session('error'))
            <div class="text-center">
                <x-form.sessionStatus :status="session('status')" />
                <x-form.sessionError :error="session('error')" />
            </div>
        @endif

        @if (count($providers))
            <div class="flex justify-center gap-2 text-sm font-semibold">
                @foreach ($providers as $provider)
                    <div class="w-1/2" wire:key="{{ $provider->name }}">
                        <x-form.linkButton href="/oauth/{{ $provider->value }}" full>
                            <x-icons.arrowRightEndOnRectangle class="w-5 h-5" />
                            {{ $provider->name }}
                        </x-form.linkButton>
                    </div>
                @endforeach
            </div>
            <div class="relative flex items-center">
                <div class="flex-grow border-t border-light-base-300 dark:border-base-500"></div>
                <span class="flex-shrink mx-4">Or continue with</span>
                <div class="flex-grow border-t border-light-base-300 dark:border-base-500"></div>
            </div>
        @endif

        <x-form wire:submit="send" class="flex flex-col gap-4">
            <x-form.input name="form.email" label="{{ __('Email') }}" type="email" required autofocus />
            <x-form.input name="form.password" label="{{ __('Password') }}" type="password" required />
            <x-form.checkbox name="form.remember" label="{{ __('Remember me') }}" />
            <x-form.submit label="{{ __('Sign in') }}" target="send" />
        </x-form>

        <div class="flex flex-col gap-2 text-center">
            @if (Route::has('forgot.password'))
                <x-form.text>
                    <x-form.link wire:navigate href="{{ route('forgot.password') }}">
                        {{ __('Forgot your password?') }}
                    </x-form.link>
                </x-form.text>
            @endif

            @if (Route::has('register'))
                <x-form.text>
                    {{ __('Don\'t have an account?') }}

                    <x-form.link wire:navigate href="{{ route('register') }}">
                        {{ __('Sign up') }}
                    </x-form.link>
                </x-form.text>
            @endif
        </div>
    </x-layouts.guestMain>
</div>
