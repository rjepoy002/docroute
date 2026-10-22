@extends('layouts.docroute')
@section('title', 'Departments')
@section('content')
<x-management-heading title="Departments" :create-route="route('admin.departments.create')" />
<p class="mb-4 text-sm text-slate-500">Deactivation prevents new assignments. Existing accounts and department history are preserved.</p>
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
<table class="w-full text-left text-sm"><caption class="sr-only">Departments and assigned account totals</caption><thead class="bg-slate-100 text-slate-600"><tr><th class="p-4">Department</th><th class="p-4">Code</th><th class="p-4">Assigned accounts</th><th class="p-4">Status</th><th class="p-4">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
@forelse($departments as $department)
<tr><td class="p-4 font-medium">{{ $department->name }}</td><td class="p-4">{{ $department->code }}</td><td class="p-4">{{ $department->users_count }}</td><td class="p-4"><x-status-badge :active="$department->is_active" /></td><td class="p-4"><a href="{{ route('admin.departments.edit', $department) }}" class="inline-block rounded-lg px-3 py-3 font-medium text-blue-700 hover:bg-blue-50" aria-label="Edit {{ $department->name }}">Edit</a></td></tr>
@empty<tr><td colspan="5" class="p-10 text-center text-slate-500">No departments found.</td></tr>@endforelse
</tbody></table></div><div class="mt-5">{{ $departments->links() }}</div>
@endsection
