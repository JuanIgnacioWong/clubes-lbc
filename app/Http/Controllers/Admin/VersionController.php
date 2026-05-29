<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubmissionVersion;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VersionController extends Controller
{
    public function index(): View
    {
        $versions = SubmissionVersion::query()
            ->with(['submission.season', 'submission.division', 'submission.club'])
            ->latest('updated_at')
            ->paginate(25);

        return view('admin.versions.index', compact('versions'));
    }

    public function updateStatus(Request $request, SubmissionVersion $version): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                SubmissionVersion::STATUS_RECEIVED,
                SubmissionVersion::STATUS_UNDER_REVIEW,
                SubmissionVersion::STATUS_ACCEPTED,
                SubmissionVersion::STATUS_REJECTED,
                SubmissionVersion::STATUS_REPLACED,
            ])],
        ]);

        $newStatus = $validated['status'];
        if ($version->status !== $newStatus) {
            $version->update(['status' => $newStatus]);
            AuditLogger::log('version_status_changed', 'submission_version', $version, 'Estado de versión actualizado.', $request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Estado de versión actualizado.',
                'version' => [
                    'id' => $version->id,
                    'status' => $version->status,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Estado de versión actualizado.');
    }

    public function destroy(Request $request, SubmissionVersion $version): JsonResponse|RedirectResponse
    {
        $versionId = $version->id;
        $version->delete();

        AuditLogger::log('version_deleted', 'submission_version', $version, 'Versión eliminada.', $request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Versión eliminada.',
                'version' => [
                    'id' => $versionId,
                ],
            ]);
        }

        return redirect()->route('admin.versions.index')->with('success', 'Versión eliminada.');
    }
}
