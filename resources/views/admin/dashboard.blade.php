@extends('layouts.docroute')
@section('title', 'Dashboard')
@section('content')
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-widest text-blue-600">Administration workspace</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Administrator Dashboard</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Welcome, {{ auth()->user()->name }}. Here's an overview of your organization.</p>
    </div>
    <section aria-labelledby="organization-heading">
        <h2 id="organization-heading" class="mb-4 text-base font-semibold text-slate-900">Organization Overview</h2>
        <div class="grid gap-4 sm:grid-cols-3 lg:gap-6">
            @foreach([['Total Users', $totalUsers, 'users'], ['Active Users', $activeUsers, 'completed'], ['Total Departments', $totalDepartments, 'building']] as [$label, $count, $icon])
                <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3"><h3 class="text-sm font-medium text-slate-500">{{ $label }}</h3><span class="rounded-lg bg-blue-50 p-2.5 text-blue-600"><x-docroute-icon :name="$icon" /></span></div>
                    <p class="mt-4 text-3xl font-bold tabular-nums text-slate-900">{{ number_format($count) }}</p>
                </article>
            @endforeach
        </div>
    </section>
    @include('dashboard.document-overview')
    @include('dashboard.recent-documents')
@endsection
