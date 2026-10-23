<form method="post" action="{{ route('profile.complete.store') }}" class="auth-form" data-auth-form>
    @csrf
    <div class="auth-field">
        <label for="name">Full Name <span aria-hidden="true">*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', ($completion ?? false) ? session('profile_suggested_name', $user->name ?: \App\Models\User::emailDisplayName($user->email)) : $user->name) }}" required autofocus maxlength="255" autocomplete="name" aria-describedby="name-error" @if($errors->has('name')) aria-invalid="true" @endif>
        <x-input-error id="name-error" :messages="$errors->get('name')" />
    </div>
    <div class="auth-field">
        <label for="email">Email Address</label>
        <input id="email" type="email" value="{{ $user->email }}" readonly aria-describedby="email-help email-error">
        <p id="email-help" class="mt-1 text-xs text-slate-500">Your account email cannot be changed here.</p>
        <x-input-error id="email-error" :messages="$errors->get('email')" />
    </div>
    <div class="auth-field">
        <label for="department_id">Department <span aria-hidden="true">*</span></label>
        <select id="department_id" name="department_id" required aria-describedby="department_id-error" @if($errors->has('department_id')) aria-invalid="true" @endif>
            <option value="">Select department</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>{{ $department->display_name }}</option>
            @endforeach
        </select>
        <x-input-error id="department_id-error" :messages="$errors->get('department_id')" />
    </div>
    <div class="auth-field">
        <label for="designation">Designation / Position <span aria-hidden="true">*</span></label>
        <input id="designation" name="designation" value="{{ old('designation', $user->designation) }}" required maxlength="255" autocomplete="organization-title" aria-describedby="designation-error" @if($errors->has('designation')) aria-invalid="true" @endif>
        <x-input-error id="designation-error" :messages="$errors->get('designation')" />
    </div>
    <div class="auth-field">
        <label for="contact_number">Contact Number <span class="font-normal text-slate-500">(optional)</span></label>
        <input id="contact_number" name="contact_number" type="tel" value="{{ old('contact_number', $user->contact_number) }}" maxlength="40" autocomplete="tel" aria-describedby="contact_number-error" @if($errors->has('contact_number')) aria-invalid="true" @endif>
        <x-input-error id="contact_number-error" :messages="$errors->get('contact_number')" />
    </div>
    @if($errors->has('role') || $errors->has('is_active') || $errors->has('google_id'))
        <p role="alert" class="text-sm text-red-600">Your account permissions cannot be changed here.</p>
    @endif
    <button class="auth-submit" type="submit" data-loading-label="Saving profile…">Save Profile &amp; Continue <span aria-hidden="true">&rarr;</span></button>
</form>
