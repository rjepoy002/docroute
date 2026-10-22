@extends('layouts.docroute')
@section('title', 'Dashboard')
@section('content')
<div class="mb-8 flex flex-wrap items-center justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-widest text-blue-600">Document workspace</p><h1 class="mt-2 text-2xl font-bold text-slate-900">Welcome, {{ auth()->user()->name }}</h1><p class="mt-2 text-sm text-slate-500">Manage your incoming documents and track work across PALECO.</p></div>@can('create', \App\Models\Document::class)<a href="{{ route('documents.create') }}" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Create document</a>@endcan</div>
@include('dashboard.document-overview')
@include('dashboard.recent-documents')
@endsection
