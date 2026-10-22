@extends('layouts.docroute')
@section('title', $department->exists ? 'Edit Department' : 'Create Department')
@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ $department->exists ? 'Edit department' : 'Create department' }}</h1>
<form method="POST" action="{{ $department->exists ? route('admin.departments.update', $department) : route('admin.departments.store') }}" class="max-w-2xl space-y-6 rounded-xl border border-slate-200 bg-white p-6" x-data @submit="if (!confirm('Save department details and status?')) $event.preventDefault()">
@csrf @if($department->exists) @method('PUT') @endif
<div><label for="name" class="block text-sm font-medium">Department name</label><input id="name" name="name" value="{{ old('name', $department->name) }}" required maxlength="255" class="mt-2 w-full rounded-lg border-slate-300"></div>
<div><label for="code" class="block text-sm font-medium">Department code</label><input id="code" name="code" value="{{ old('code', $department->code) }}" required maxlength="20" pattern="[A-Za-z0-9_-]+" class="mt-2 w-full rounded-lg border-slate-300"><p class="mt-2 text-xs text-slate-500">Letters, numbers, hyphens and underscores. Saved in uppercase.</p></div>
<div><label for="is_active" class="block text-sm font-medium">Status</label><select id="is_active" name="is_active" class="mt-2 w-full rounded-lg border-slate-300"><option value="1" @selected(old('is_active', $department->is_active) == 1)>Active</option><option value="0" @selected(old('is_active', $department->is_active) == 0)>Inactive</option></select><p class="mt-2 text-xs text-slate-500">Inactive departments cannot receive new account assignments.</p></div>
<div class="flex flex-wrap gap-3"><button class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Save department</button><a href="{{ route('admin.departments.index') }}" class="rounded-lg border px-5 py-3 text-sm">Cancel</a></div>
</form>
@endsection
