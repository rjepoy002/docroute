<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee access | PALECO DocRoute v2</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell">
    <a class="auth-skip" href="#auth-content">Skip to form</a>
    <div class="auth-layout">
        <aside class="auth-brand" aria-label="PALECO DocRoute">
            <a href="{{ url('/') }}" class="auth-wordmark">
                @if(is_file(public_path('images/paleco-logo.png')))
                    <img class="auth-logo" src="{{ asset('images/paleco-logo.png') }}" alt="PALECO logo" width="88" height="88">
                @endif
                <span><strong>PALECO</strong><span>Palawan Electric Cooperative</span></span>
            </a>
            <div class="auth-brand-story">
                <span class="auth-eyebrow"><span></span> CONNECTED. ORGANIZED. SECURE.</span>
                <h1>Powering Better<br>Document<br><span>Workflows.</span></h1>
                <p>A secure and streamlined document routing experience for PALECO employees.</p>
                <div class="auth-security"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"/><path d="m8 12 3 3 5-6"/></svg>Secure Internal Document Management</div>
            </div>
            <footer class="auth-brand-footer"><span>PALECO DocRoute v2</span><span>Built for better connections.</span></footer>
            <div class="auth-circuit" aria-hidden="true"></div>
        </aside>
        <main id="auth-content" class="auth-main" tabindex="-1">
            <div class="auth-card">{{ $slot }}</div>
            <footer class="auth-form-footer">PALECO DocRoute v2 <span aria-hidden="true">&middot;</span> Employee Portal</footer>
        </main>
    </div>
</body>
</html>
