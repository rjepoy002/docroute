@extends('layouts.docroute')
@section('title', 'Users')
@section('content')
<x-management-heading title="Users" :create-route="route('admin.users.create')" />
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
<table class="w-full text-left text-sm"><caption class="sr-only">Employee and administrator accounts</caption><thead class="bg-slate-100 text-slate-600"><tr><th class="p-4">Account</th><th class="p-4">Department</th><th class="p-4">Role</th><th class="p-4">Status</th><th class="p-4">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
@forelse($users as $user)
<tr><td class="p-4"><p class="font-medium">{{ $user->name }}</p><p class="text-slate-500">{{ $user->email }}</p></td><td class="p-4">{{ $user->department?->display_name ?? 'Unassigned' }} @if($user->department && !$user->department->is_active)<span class="text-xs text-slate-500">(inactive)</span>@endif</td><td class="p-4">{{ ucfirst($user->role) }}</td><td class="p-4"><x-status-badge :active="$user->is_active" /></td><td class="p-4"><a href="{{ route('admin.users.edit', $user) }}" class="inline-block rounded-lg px-3 py-3 font-medium text-blue-700 hover:bg-blue-50" aria-label="Edit {{ $user->name }}">Edit</a></td></tr>
@empty<tr><td colspan="5" class="p-10 text-center text-slate-500">No accounts found.</td></tr>@endforelse
</tbody></table></div><div class="mt-5">{{ $users->links() }}</div>
@endsection
