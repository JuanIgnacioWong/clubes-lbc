<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clubId = $this->route('club')?->id;
        $seasonId = (int) $this->input('season_id');
        $divisionId = (int) $this->input('division_id');

        return [
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('clubs', 'slug')
                    ->ignore($clubId)
                    ->where(fn ($q) => $q->where('season_id', $seasonId)->where('division_id', $divisionId)),
            ],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email:rfc', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $seasonId = (int) $this->input('season_id');
            $divisionId = (int) $this->input('division_id');

            $divisionSeasonId = \App\Models\Division::query()->whereKey($divisionId)->value('season_id');

            if ($divisionSeasonId !== null && (int) $divisionSeasonId !== $seasonId) {
                $validator->errors()->add('division_id', 'La división seleccionada no pertenece a la temporada indicada.');
            }
        });
    }
}
