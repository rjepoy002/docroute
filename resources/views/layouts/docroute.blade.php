<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | PALECO DocRoute v2</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
@php
    $documentCounts = $documentCounts ?? app(\App\Support\DocumentOverview::class)->forUser(auth()->user());
@endphp
<div x-data="{ sidebarOpen: false }" x-effect="document.body.style.overflow = sidebarOpen ? 'hidden' : ''" @keydown.escape.window="if (sidebarOpen) { sidebarOpen = false; $refs.menu.focus(); }" @resize.window="if (window.innerWidth >= 1024) sidebarOpen = false" class="min-h-screen">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:p-3">Skip to content</a>
    <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-slate-950/60 lg:hidden" aria-hidden="true"></div>
    <aside id="application-sidebar" :class="sidebarOpen ? 'flex' : 'hidden lg:flex'" class="fixed inset-y-0 left-0 z-40 w-72 max-w-[85vw] flex-col bg-slate-900 text-slate-300" @keydown.tab="if (sidebarOpen) { const items = [...$el.querySelectorAll('a[href], button')].filter(el => el.getClientRects().length); const first = items[0]; const last = items[items.length - 1]; if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); } else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); } }">
        <div class="flex h-24 shrink-0 items-center justify-between border-b border-white/10 px-6">
            <div><p class="text-xl font-bold tracking-widest text-white">PALECO</p><p class="mt-1 text-sm text-slate-400">DocRoute <span class="ml-1 rounded bg-blue-500/20 px-1.5 py-0.5 text-xs text-blue-300">v2</span></p></div>
            <button type="button" @click="sidebarOpen = false; $refs.menu.focus()" class="rounded-lg p-3 text-white hover:bg-white/10 lg:hidden" aria-label="Close navigation"><x-docroute-icon name="close" /></button>
        </div>
        <nav class="flex-1 space-y-7 overflow-y-auto px-4 py-6" aria-label="Main navigation">
            <div>
                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-widest text-slate-500">Main</p>
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" @if(request()->routeIs('admin.dashboard', 'dashboard')) aria-current="page" @endif class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.dashboard', 'dashboard') ? 'bg-blue-600 text-white' : 'hover:bg-white/10' }}"><x-docroute-icon name="dashboard" /> Dashboard</a>
            </div>
            <div>
                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-widest text-slate-500">Documents</p>
                @can('create', \App\Models\Document::class)
                    <a href="{{ route('documents.create') }}" @if(request()->routeIs('documents.create')) aria-current="page" @endif class="mb-2 flex items-center gap-3 rounded-lg px-3 py-3 text-sm {{ request()->routeIs('documents.create') ? 'bg-blue-600 text-white' : 'text-blue-300 hover:bg-white/10' }}"><x-docroute-icon />Create Document</a>
                @endcan
                @foreach(['incoming' => 'Incoming', 'outgoing' => 'Outgoing', 'pending' => 'Pending', 'unseen' => 'Unseen', 'index' => 'All Documents'] as $key => $label)
                    <a href="{{ route('documents.'.$key) }}" @if(request()->routeIs('documents.'.$key)) aria-current="page" @endif class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm {{ request()->routeIs('documents.'.$key) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-white/10' }}"><x-docroute-icon :name="$key" /><span class="flex-1">{{ $label }}</span>@if($key !== 'index')<span class="rounded-md bg-white/10 px-2 py-0.5 text-xs tabular-nums">{{ $documentCounts[$key] }}</span>@endif</a>
                @endforeach
                @if(auth()->user()->isAdmin())<p class="mt-2 px-3 text-xs text-slate-500">All Documents includes organization monitoring.</p>@endif
            </div>
            @if(auth()->user()->isAdmin())
                <div>
                    <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-widest text-slate-500">Administration</p>
                    @foreach(['users' => ['Users', 'users'], 'departments' => ['Departments', 'building']] as $module => [$label, $icon])
                        <a href="{{ route('admin.'.$module.'.index') }}" @if(request()->routeIs('admin.'.$module.'.*')) aria-current="page" @endif class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm {{ request()->routeIs('admin.'.$module.'.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-white/10' }}"><x-docroute-icon :name="$icon" /><span>{{ $label }}</span></a>
                    @endforeach
                    <span aria-disabled="true" class="flex items-center gap-3 px-3 py-3 text-sm text-slate-400"><x-docroute-icon name="settings" /><span class="flex-1">Settings</span><span class="text-xs text-slate-500">Soon</span></span>
                </div>
            @endif
        </nav>
        <div class="shrink-0 border-t border-white/10 p-4">
            <a href="{{ route('profile.edit') }}" class="flex min-w-0 items-center gap-3 rounded-lg p-2 hover:bg-white/5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-500/20 font-semibold text-blue-200">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                <span class="min-w-0"><span class="block truncate text-sm font-medium text-white">{{ auth()->user()->name }}</span><span class="block text-xs text-slate-400">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Employee' }} &middot; View profile</span></span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-sm hover:bg-white/10"><x-docroute-icon name="logout" />Log out</button></form>
        </div>
    </aside>
    <div class="min-w-0 lg:pl-72" :inert="sidebarOpen">
        <header class="flex min-h-20 items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 sm:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <button x-ref="menu" type="button" @click="sidebarOpen = true; $nextTick(() => document.querySelector('#application-sidebar button').focus())" :aria-expanded="sidebarOpen.toString()" aria-controls="application-sidebar" aria-label="Open navigation" class="rounded-lg p-3 hover:bg-slate-100 lg:hidden"><x-docroute-icon name="menu" /></button>
                <div><p class="text-sm font-semibold lg:hidden">PALECO DocRoute v2</p><p class="text-sm text-slate-500"><span class="hidden sm:inline">Workspace / </span>@yield('title', 'Dashboard')</p></div>
            </div>
            <div class="flex shrink-0 items-center gap-3 sm:gap-5">
                <span class="hidden rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 sm:inline">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Employee' }}</span>
                <button type="button" disabled aria-label="Notifications unavailable" title="Notifications are not yet available" class="rounded-lg border border-slate-200 p-3 text-slate-400"><x-docroute-icon name="bell" /></button>
            </div>
        </header>
        <main id="main-content" tabindex="-1" class="mx-auto max-w-screen-2xl px-4 py-8 sm:px-8 lg:py-10">
            @if(session('success'))<div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800"><p class="font-semibold">Please correct the following:</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
        <footer class="px-4 pb-6 text-xs text-slate-500 sm:px-8">PALECO &middot; Document Routing System</footer>
    </div>
</div>
</body>
</html>
