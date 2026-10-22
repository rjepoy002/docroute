@extends('layouts.docroute')
@section('title', $user->exists ? 'Edit Account' : 'Create Account')
@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ $user->exists ? 'Edit account' : 'Create account' }}</h1>
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="max-w-2xl space-y-5 rounded-xl border border-slate-200 bg-white p-6" x-data @submit="if (!confirm('Save account details, role and status?')) $event.preventDefault()">
@csrf @if($user->exists) @method('PUT') @endif
<div><label for="name" class="block text-sm font-medium">Full name</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" class="mt-2 w-full rounded-lg border-slate-300"></div>
<div><label for="email" class="block text-sm font-medium">Email</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" class="mt-2 w-full rounded-lg border-slate-300"></div>
<div><label for="department_id" class="block text-sm font-medium">Department</label><select id="department_id" name="department_id" required class="mt-2 w-full rounded-lg border-slate-300"><option value="">Select an active department</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>{{ $department->name }}{{ $department->is_active ? '' : ' (inactive, existing assignment)' }}</option>@endforeach</select></div>
<div><label for="role" class="block text-sm font-medium">Role</label><select id="role" name="role" class="mt-2 w-full rounded-lg border-slate-300"><option value="employee" @selected(old('role', $user->role) === 'employee')>Employee</option><option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator</option></select></div>
<div><label for="is_active" class="block text-sm font-medium">Status</label><select id="is_active" name="is_active" class="mt-2 w-full rounded-lg border-slate-300"><option value="1" @selected(old('is_active', $user->is_active) == 1)>Active</option><option value="0" @selected(old('is_active', $user->is_active) == 0)>Inactive</option></select></div>
@if($user->id === auth()->id())<p class="text-sm text-slate-500">Your own account must remain active with the administrator role.</p>@endif
@if(!$user->exists)
@include('admin.users.password-fields', ['prefix' => 'create'])
@endif
<div class="flex flex-wrap gap-3"><button class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Save account</button><a href="{{ route('admin.users.index') }}" class="rounded-lg border px-5 py-3 text-sm">Cancel</a></div>
</form>
@if($user->exists)
<section class="mt-8 max-w-2xl rounded-xl border border-slate-200 bg-white p-6" aria-labelledby="reset-heading"><h2 id="reset-heading" class="text-lg font-semibold">Reset account password</h2><p class="mt-2 text-sm text-slate-500">Confirm your administrator password. Share the new password privately with the account owner. Existing sessions will require login again.</p>
<form method="POST" action="{{ route('admin.users.password', $user) }}" class="mt-5 space-y-5" x-data @submit="if (!confirm('Reset this account password and require login again?')) $event.preventDefault()">@csrf @method('PUT')
<div><label for="current_password" class="block text-sm font-medium">Your administrator password</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="mt-2 w-full rounded-lg border-slate-300"></div>
@include('admin.users.password-fields', ['prefix' => 'reset'])
<button class="rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Reset password</button>
</form></section>
@endif
@endsection
