<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class StoreCorrectionSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $logoMaxKb = ((int) Setting::getValue('max_logo_mb', '2')) * 1024;
        $docMaxKb = ((int) Setting::getValue('max_documents_mb', '10')) * 1024;

        return [
            'responsible_name' => ['required', 'string', 'min:3', 'max:150'],
            'phone' => ['required', 'string', 'min:8', 'max:40'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'club_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:'.$logoMaxKb],
            'payment_receipt' => ['nullable', 'file', 'mimes:pdf,xls,xlsx,docx', 'max:'.$docMaxKb],
            'players_roster' => ['nullable', 'file', 'mimes:pdf,xls,xlsx,docx', 'max:'.$docMaxKb],
            'observations' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $hasSomeFile = $this->hasFile('club_logo') || $this->hasFile('payment_receipt') || $this->hasFile('players_roster');

            if (! $hasSomeFile) {
                $validator->errors()->add('club_logo', 'Debes adjuntar al menos un archivo para la corrección.');
            }
        });
    }
}
