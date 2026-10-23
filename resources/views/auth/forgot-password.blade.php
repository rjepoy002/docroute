<x-guest-layout>
    <a class="auth-back" href="{{ route('login') }}">&larr; Back to sign in</a>
    <header class="auth-heading"><span class="auth-kicker">ACCOUNT RECOVERY</span><h2>Forgot Password?</h2><p>Enter your account email and we'll send you a link to reset your password.</p></header>
    <x-auth-session-status class="auth-feedback" :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}" class="auth-form" data-auth-form>
        @csrf
        <div class="auth-field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email" aria-describedby="email-error" @if($errors->has('email')) aria-invalid="true" @endif>
            <x-input-error id="email-error" :messages="$errors->get('email')" />
        </div>
        <button class="auth-submit" type="submit" data-loading-label="Sending link…">Send Reset Link <span aria-hidden="true">&rarr;</span></button>
    </form>
</x-guest-layout>
