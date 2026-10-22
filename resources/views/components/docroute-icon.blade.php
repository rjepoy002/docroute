@props(['name' => 'document'])
<svg {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard') <path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/> @break
        @case('users') <circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5"/> @break
        @case('building') <path d="M4 21V3h16v18M2 21h20M9 21v-4h6v4M8 7h1m6 0h1M8 11h1m6 0h1"/> @break
        @case('bell') <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
        @case('logout') <path d="M9 4H4v16h5m6-12 4 4-4 4M8 12h12"/> @break
        @case('incoming') <path d="M12 3v12m-4-4 4 4 4-4M4 14v6h16v-6"/> @break
        @case('outgoing') <path d="M12 15V3m-4 4 4-4 4 4M4 14v6h16v-6"/> @break
        @case('pending') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('completed') <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/> @break
        @default <path d="M14 2H5v20h14V7zM14 2v5h5M8 12h8M8 16h6"/>
    @endswitch
</svg>
