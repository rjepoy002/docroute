<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true;
    }

    public static function nameRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'regex:/^[A-Za-z]\\.?$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->only('first_name', 'last_name', 'middle_initial'))->map(fn ($value) => is_string($value) ? trim($value) : $value)->all());
    }

    public function rules(): array
    {
        $rules = self::nameRules();
        foreach (['id', 'user_id', 'name', 'email', 'department_id', 'role', 'is_admin', 'is_active', 'approved', 'approval_status', 'approved_at', 'google_id', 'password', 'permissions', 'profile_completed_at'] as $field) {
            $rules[$field] = ['prohibited'];
        }

        return $rules;
    }

    public static function normalizeNames(array $data): array
    {
        return [
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'middle_initial' => filled($data['middle_initial'] ?? null) ? strtoupper(rtrim(trim($data['middle_initial']), '.')) : null,
        ];
    }
}
