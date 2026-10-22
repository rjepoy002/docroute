<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class DocumentActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_active;
    }

    public function rules(): array
    {
        return [
            'route_id' => ['required', 'integer', 'min:1'],
            'reason' => [$this->routeIs('documents.decline') ? 'required' : 'nullable', 'string', 'max:5000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
