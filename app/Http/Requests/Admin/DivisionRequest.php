<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $divisionId = $this->route('division')?->id;
        $seasonId = (int) $this->input('season_id');

        return [
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('divisions', 'slug')->ignore($divisionId)->where(fn ($q) => $q->where('season_id', $seasonId)),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'payment_url' => ['nullable', 'url:http,https', 'max:255'],
            'payment_button_text' => ['nullable', 'string', 'max:120'],
            'payment_description' => ['nullable', 'string', 'max:255'],
            'payment_is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $isActive = filter_var($this->input('payment_is_active'), FILTER_VALIDATE_BOOL);
            $hasUrl = filled($this->input('payment_url'));

            if ($isActive && ! $hasUrl) {
                $validator->errors()->add('payment_url', 'Debe existir URL de pago para activar el link.');
            }
        });
    }
}
