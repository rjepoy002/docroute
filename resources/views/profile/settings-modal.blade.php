@php($profileUser = auth()->user())
<dialog id="profile-settings" class="profile-dialog profile-settings" aria-labelledby="profile-settings-title" data-profile-settings data-values="{{ json_encode(['first_name' => $profileUser->first_name ?: session('google_name_suggestions.first_name', ''), 'last_name' => $profileUser->last_name ?: session('google_name_suggestions.last_name', ''), 'middle_initial' => $profileUser->middle_initial ?? '']) }}">
    <header class="flex items-start justify-between gap-4">
        <div><h2 id="profile-settings-title" class="text-xl font-semibold">Profile Settings</h2><p class="mt-1 text-sm text-slate-500">Manage your personal information.</p></div>
        <button type="button" data-profile-close aria-label="Close Profile Settings" class="rounded-lg p-3 hover:bg-slate-100">&times;</button>
    </header>
    <div class="my-6 flex items-center gap-4 rounded-xl bg-slate-50 p-4">
        @if(session('google_profile_photo'))
            <img src="{{ session('google_profile_photo') }}" alt="Your Google profile photo" class="h-14 w-14 rounded-full object-cover" referrerpolicy="no-referrer">
        @else
            <span data-profile-initials class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-slate-800 font-semibold text-white">{{ mb_strtoupper(mb_substr($profileUser->name, 0, 1)) }}</span>
        @endif
        <div class="min-w-0"><p data-current-user-name class="break-words font-semibold">{{ $profileUser->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $profileUser->department?->display_name ?? 'Unassigned department' }}</p><p class="mt-1 text-xs text-slate-500">{{ ucfirst($profileUser->role) }} &middot; {{ $profileUser->google_id ? 'Google connected' : 'Google not connected' }}</p></div>
    </div>
    <form method="post" action="{{ route('profile.update') }}" data-profile-settings-form class="auth-form">
        @csrf @method('patch')
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['first_name' => 'First Name', 'last_name' => 'Last Name'] as $field => $label)
                <div class="auth-field"><label for="settings-{{ $field }}">{{ $label }} *</label><input id="settings-{{ $field }}" name="{{ $field }}" required maxlength="100" autocomplete="{{ $field === 'first_name' ? 'given-name' : 'family-name' }}" aria-describedby="settings-{{ $field }}-error"><p id="settings-{{ $field }}-error" class="mt-1 text-xs text-red-700" data-field-error="{{ $field }}"></p></div>
            @endforeach
        </div>
        <div class="auth-field"><label for="settings-middle_initial">Middle Initial (optional)</label><input id="settings-middle_initial" name="middle_initial" maxlength="2" pattern="[A-Za-z][.]?" autocomplete="additional-name" aria-describedby="settings-middle_initial-error"><p id="settings-middle_initial-error" class="mt-1 text-xs text-red-700" data-field-error="middle_initial"></p></div>
        <div class="auth-field"><label for="settings-email">Email Address</label><input id="settings-email" type="email" value="{{ $profileUser->email }}" readonly></div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="auth-field"><label for="settings-department">Department</label><input id="settings-department" value="{{ $profileUser->department?->display_name ?? 'Unassigned' }}" readonly></div>
            <div class="auth-field"><label for="settings-role">Role</label><input id="settings-role" value="{{ ucfirst($profileUser->role) }}" readonly></div>
        </div>
        <p data-profile-message role="alert" class="text-sm text-red-700"></p>
        <footer class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-4"><button type="button" data-profile-close class="rounded-lg border border-slate-300 px-5 py-3 text-sm">Cancel</button><button type="submit" class="rounded-lg bg-slate-800 px-5 py-3 text-sm font-semibold text-white disabled:opacity-60">Save Changes</button></footer>
    </form>
</dialog>
<div data-profile-success role="status" class="fixed bottom-5 right-5 z-50 max-w-[calc(100%-40px)] rounded-lg bg-emerald-800 px-5 py-3 text-sm text-white shadow-lg" hidden></div>