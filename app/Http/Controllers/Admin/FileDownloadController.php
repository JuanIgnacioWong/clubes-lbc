<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubmissionVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    public function __invoke(Request $request, SubmissionVersion $version, string $type): StreamedResponse
    {
        $field = match ($type) {
            'logo' => 'club_logo_path',
            'comprobante' => 'payment_receipt_path',
            'nomina' => 'players_roster_path',
            default => abort(404),
        };

        $path = $version->{$field};

        abort_if(blank($path), 404);
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download($path);
    }
}
