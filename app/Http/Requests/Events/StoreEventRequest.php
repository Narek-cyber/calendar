<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'summary' => ['required', 'string'],
            'location' => ['nullable', 'string'],
            'start' => ['required', 'date', 'after_or_equal:now', 'before:end'],
            'end' => ['required', 'date', 'after:start'],
            'description' => ['nullable', 'string'],
            'timezone' => ['nullable', 'string'],
        ];
    }
}
