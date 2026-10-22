<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->is_active;
    }

    protected function prepareForValidation(): void
    {
        foreach (['name', 'email'] as $field) {
            if (is_string($this->input($field))) {
                $value = trim($this->input($field));
                $this->merge([$field => $field === 'email' ? strtolower($value) : $value]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'role' => ['required', Rule::in(['admin', 'employee'])],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'is_active' => ['required', 'boolean'],
            'password' => $this->isMethod('post') ? ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()] : ['prohibited'],
        ];
    }
}
