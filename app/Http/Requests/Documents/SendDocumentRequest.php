<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_active;
    }

    public function rules(): array
    {
        return [
            'title' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')->where('is_active', true)],
            'recipient_id' => ['required', 'integer', Rule::exists('users', 'id')->where('is_active', true)->where('department_id', $this->integer('department_id'))],
            'route_id' => [$this->isMethod('post') ? 'prohibited' : 'required', 'integer'],
        ];
    }
}
