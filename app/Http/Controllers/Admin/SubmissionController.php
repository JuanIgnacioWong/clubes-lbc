<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Services\AuditLogger;
use App\Services\EventNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Submission::query()->with(['season', 'division', 'club', 'versions']);

        if ($request->filled('season_id')) {
            $query->where('season_id', $request->integer('season_id'));
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->integer('division_id'));
        }

        if ($request->filled('club_id')) {
            $query->where('club_id', $request->integer('club_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }

        if ($request->filled('submission_status')) {
            $query->where('submission_status', $request->string('submission_status'));
        }

        $submissions = $query->latest('updated_at')->paginate(20)->withQueryString();

        return view('admin.submissions.index', compact('submissions'));
    }

    public function show(Submission $submission): View
    {
        $submission->load(['season', 'division', 'club', 'versions']);

        return view('admin.submissions.show', compact('submission'));
    }

    public function updateStatus(Request $request, Submission $submission): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Submission::STATUS_RECEIVED,
                Submission::STATUS_UNDER_REVIEW,
                Submission::STATUS_ACCEPTED,
                Submission::STATUS_REJECTED,
            ])],
        ]);

        $oldStatus = $submission->submission_status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            $submission->update(['submission_status' => $newStatus]);
            AuditLogger::log('submission_status_changed', 'submission', $submission, 'Estado de postulación actualizado.', $request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Estado de postulación actualizado.',
                'submission' => [
                    'id' => $submission->id,
                    'status' => $submission->submission_status,
                ],
            ]);
        }

        return redirect()->route('admin.submissions.index')->with('success', 'Estado de postulación actualizado.');
    }

    public function updatePaymentStatus(Request $request, Submission $submission, EventNotificationService $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => ['required', Rule::in([
                Submission::PAYMENT_PENDING,
                Submission::PAYMENT_IN_REVIEW,
                Submission::PAYMENT_PAID,
            ])],
        ]);

        $oldStatus = $submission->payment_status;
        $newStatus = $validated['payment_status'];

        if ($oldStatus !== $newStatus) {
            $submission->update(['payment_status' => $newStatus]);
            AuditLogger::log('payment_status_changed', 'submission', $submission, 'Estado de pago actualizado.', $request);
            $notifier->paymentStatusChanged($submission->fresh(['season', 'division', 'club']), $oldStatus, $newStatus);
        }

        return redirect()->back()->with('success', 'Estado de pago actualizado.');
    }

    public function updateVersionStatus(Request $request, Submission $submission, SubmissionVersion $version, EventNotificationService $notifier): RedirectResponse
    {
        abort_unless($version->submission_id === $submission->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                SubmissionVersion::STATUS_RECEIVED,
                SubmissionVersion::STATUS_UNDER_REVIEW,
                SubmissionVersion::STATUS_ACCEPTED,
                SubmissionVersion::STATUS_REJECTED,
                SubmissionVersion::STATUS_REPLACED,
            ])],
        ]);

        $oldStatus = $version->status;
        $newStatus = $validated['status'];
        $version->update(['status' => $newStatus]);

        if ($newStatus === SubmissionVersion::STATUS_ACCEPTED) {
            $submission->update([
                'active_version' => $version->id,
                'submission_status' => Submission::STATUS_ACCEPTED,
            ]);

            AuditLogger::log('version_approved', 'submission_version', $version, 'Versión aprobada.', $request);
        }

        if ($newStatus === SubmissionVersion::STATUS_REJECTED) {
            $submission->update(['submission_status' => Submission::STATUS_REJECTED]);
            AuditLogger::log('version_rejected', 'submission_version', $version, 'Versión rechazada.', $request);
        }

        if ($oldStatus !== $newStatus) {
            $notifier->versionStatusChanged($submission->fresh(['season', 'division', 'club']), $version->fresh(), $oldStatus, $newStatus);
        }

        return redirect()->back()->with('success', 'Estado de versión actualizado.');
    }

    public function allowExtraSubmission(Request $request, Submission $submission): RedirectResponse
    {
        $max = min(4, max(2, $submission->max_allowed_submissions + 1));
        $submission->update(['max_allowed_submissions' => $max]);

        AuditLogger::log('extra_submission_enabled', 'submission', $submission, 'Habilitado envío adicional.', $request);

        return redirect()->back()->with('success', 'Se habilitó un envío adicional.');
    }

    public function destroyVersion(Request $request, Submission $submission, SubmissionVersion $version): RedirectResponse
    {
        abort_unless($version->submission_id === $submission->id, 404);

        $version->delete();
        AuditLogger::log('version_deleted', 'submission_version', $version, 'Versión eliminada.', $request);

        return redirect()->back()->with('success', 'Versión eliminada.');
    }
}
