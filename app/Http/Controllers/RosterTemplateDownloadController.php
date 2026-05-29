<?php

namespace App\Http\Controllers;

use App\Services\RosterTemplateService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RosterTemplateDownloadController extends Controller
{
    public function publicDownload(RosterTemplateService $rosterTemplateService): StreamedResponse
    {
        $settings = $rosterTemplateService->getSettings();

        abort_unless($rosterTemplateService->isAvailable($settings), Response::HTTP_NOT_FOUND);

        $path = $settings['roster_template_path'];
        $downloadName = $rosterTemplateService->publicDownloadName($settings);

        return Storage::disk('private')->download($path, $downloadName);
    }

    public function adminDownload(RosterTemplateService $rosterTemplateService): StreamedResponse
    {
        $settings = $rosterTemplateService->getSettings();

        abort_unless($rosterTemplateService->exists($settings), Response::HTTP_NOT_FOUND);

        $path = $settings['roster_template_path'];
        $downloadName = $rosterTemplateService->publicDownloadName($settings);

        return Storage::disk('private')->download($path, $downloadName);
    }
}
