<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicSubmissionRequest extends FormRequest
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
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'responsible_name' => ['required', 'string', 'min:3', 'max:150'],
            'phone' => ['required', 'string', 'min:8', 'max:40'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'club_logo' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:'.$logoMaxKb],
            'payment_receipt' => ['required', 'file', 'mimes:pdf,xls,xlsx,docx', 'max:'.$docMaxKb],
            'players_roster' => ['required', 'file', 'mimes:pdf,xls,xlsx,docx', 'max:'.$docMaxKb],
            'observations' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
