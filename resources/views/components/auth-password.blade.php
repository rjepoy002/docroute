@props(['id' => 'password', 'name' => null, 'autocomplete' => 'current-password', 'invalid' => false])
<div class="auth-password" x-data="{ visible: false }">
    <input id="{{ $id }}" name="{{ $name ?? $id }}" type="password" :type="visible ? 'text' : 'password'" required autocomplete="{{ $autocomplete }}" aria-describedby="{{ $id }}-error" @if($invalid) aria-invalid="true" @endif>
    <button x-cloak type="button" @click="visible = !visible" :aria-pressed="visible" aria-controls="{{ $id }}" :aria-label="visible ? 'Hide password' : 'Show password'" class="auth-password-toggle"><span x-text="visible ? 'Hide' : 'Show'">Show</span></button>
</div>
