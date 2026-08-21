<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form class="auth-form" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="auth-field">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="auth-field">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="remember_me" class="auth-check">
                <input id="remember_me" type="checkbox" name="remember">
                <span>{{ __('Remember me') }}</span>
            </label>
        </div>
        <div class="auth-actions">
            <a href="{{ route('register') }}">Create an account</a>
            <button class="button" type="submit">{{ __('Log in') }}</button>
        </div>
    </form>
</x-guest-layout>
