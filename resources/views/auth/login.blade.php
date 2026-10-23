<x-guest-layout>
    <div class="auth-notice">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v2"/></svg>
        <div><strong>Official PALECO Email Recommended</strong><p>For security and account verification, employees are strongly encouraged to sign in using their official PALECO email address.</p></div>
    </div>
    <header class="auth-heading"><span class="auth-kicker">EMPLOYEE ACCESS</span><h2>Welcome Back</h2><p>Sign in to your PALECO account.</p></header>
    <x-auth-session-status class="auth-feedback" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="auth-form" data-auth-form>
        @csrf
        <div class="auth-field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autofocus autocomplete="username" inputmode="email" aria-describedby="email-error" @if($errors->has('email')) aria-invalid="true" @endif>
            <x-input-error id="email-error" :messages="$errors->get('email')" />
        </div>
        <div class="auth-field">
            <label for="password">Password</label>
            <x-auth-password id="password" autocomplete="current-password" :invalid="$errors->has('password')" />
            <x-input-error id="password-error" :messages="$errors->get('password')" />
        </div>
        <div class="auth-options">
            <label class="auth-check" for="remember"><input id="remember" type="checkbox" name="remember" @checked(old('remember'))>Remember Me</label>
            <a href="{{ route('password.request') }}">Forgot Password?</a>
        </div>
        <button class="auth-submit" type="submit" data-loading-label="Signing in…">Sign In <span aria-hidden="true">&rarr;</span></button>
    </form>
    @if(config('services.google.client_id'))
        <div class="auth-divider"><span>or continue with</span></div>
        <a class="auth-google" href="{{ route('auth.google.redirect') }}">
            <svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6C44.4 38.02 46.98 31.89 46.98 24.55Z"/><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.93 7.53 2.56 10.78l7.97-6.19Z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.15 1.45-4.92 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/></svg>
            Continue with Google
        </a>
    @endif
    <p class="auth-help">Access is reserved for authorized PALECO employees.</p>
</x-guest-layout>
