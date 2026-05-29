<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PublicAssetController extends Controller
{
    public function institutionalLogo(): Response
    {
        $path = Setting::platformLogoPath();

        abort_if($path === '' || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
