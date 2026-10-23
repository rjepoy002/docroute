<x-guest-layout>
    <a class="auth-back" href="{{ route('login') }}">&larr; Back to sign in</a>
    <header class="auth-heading"><span class="auth-kicker">ACCOUNT RECOVERY</span><h2>Set a New Password</h2><p>Choose a secure password for your PALECO account.</p></header>
    <form method="POST" action="{{ route('password.store') }}" class="auth-form" data-auth-form>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="auth-field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" aria-describedby="email-error" @if($errors->has('email')) aria-invalid="true" @endif>
            <x-input-error id="email-error" :messages="$errors->get('email')" />
        </div>
        <div class="auth-field">
            <label for="password">New password</label>
            <x-auth-password id="password" autocomplete="new-password" :invalid="$errors->has('password')" />
            <x-input-error id="password-error" :messages="$errors->get('password')" />
        </div>
        <div class="auth-field">
            <label for="password_confirmation">Confirm new password</label>
            <x-auth-password id="password_confirmation" autocomplete="new-password" :invalid="$errors->has('password_confirmation')" />
            <x-input-error id="password_confirmation-error" :messages="$errors->get('password_confirmation')" />
        </div>
        <button class="auth-submit" type="submit" data-loading-label="Resetting password…">Reset Password <span aria-hidden="true">&rarr;</span></button>
    </form>
</x-guest-layout>
