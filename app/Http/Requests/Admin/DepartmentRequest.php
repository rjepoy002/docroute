<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() && $this->user()->is_active;
    }

    protected function prepareForValidation(): void
    {
        foreach (['name', 'code'] as $field) {
            if (is_string($this->input($field))) {
                $value = trim($this->input($field));
                $this->merge([$field => $field === 'code' ? strtoupper($value) : $value]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($this->route('department'))],
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('departments')->ignore($this->route('department'))],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
