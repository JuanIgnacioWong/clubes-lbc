<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $seasonId = $this->route('season')?->id;

        return [
            'year' => ['required', 'integer', 'between:2020,2100', Rule::unique('seasons', 'year')->ignore($seasonId)],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:50', Rule::unique('seasons', 'slug')->ignore($seasonId)],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
