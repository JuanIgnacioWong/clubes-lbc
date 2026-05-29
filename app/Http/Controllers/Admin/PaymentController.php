<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use App\Models\Submission;
use App\Services\AuditLogger;
use App\Services\EventNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Submission::query()->with(['season', 'division', 'club', 'versions']);
        $this->applyFilters($query, $request);

        $submissions = $query->latest('updated_at')->paginate(25)->withQueryString();

        $statsQuery = Submission::query();
        $this->applyFilters($statsQuery, $request, includePaymentStatus: false);

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('payment_status', Submission::PAYMENT_PENDING)->count(),
            'in_review' => (clone $statsQuery)->where('payment_status', Submission::PAYMENT_IN_REVIEW)->count(),
            'paid' => (clone $statsQuery)->where('payment_status', Submission::PAYMENT_PAID)->count(),
        ];

        $seasons = Season::query()->orderByDesc('year')->get();
        $divisions = Division::query()->orderBy('name')->get();
        $clubs = Club::query()->orderBy('name')->get();

        return view('admin.pagos.index', compact('submissions', 'stats', 'seasons', 'divisions', 'clubs'));
    }

    public function updateStatus(Request $request, Submission $submission, EventNotificationService $notifier): RedirectResponse
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
            AuditLogger::log('payment_status_changed', 'submission', $submission, 'Estado de pago actualizado desde módulo Pagos.', $request);
            $notifier->paymentStatusChanged($submission->fresh(['season', 'division', 'club']), $oldStatus, $newStatus);
        }

        return redirect()->back()->with('success', 'Estado de pago actualizado.');
    }

    public function export(Request $request)
    {
        $query = Submission::query()->with(['season', 'division', 'club', 'versions']);
        $this->applyFilters($query, $request);
        $rows = $query->latest('updated_at')->get();

        $filename = 'pagos-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Temporada', 'Division', 'Club', 'Responsable', 'Email', 'Telefono', 'Envíos', 'Pago', 'Estado', 'Actualizado',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->season->year,
                    $row->division->name,
                    $row->club->name,
                    $row->responsible_name,
                    $row->email,
                    $row->phone,
                    $row->versions->count(),
                    $row->payment_status,
                    $row->submission_status,
                    $row->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function applyFilters(Builder $query, Request $request, bool $includePaymentStatus = true): void
    {
        if ($request->filled('season_id')) {
            $query->where('season_id', $request->integer('season_id'));
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->integer('division_id'));
        }

        if ($request->filled('club_id')) {
            $query->where('club_id', $request->integer('club_id'));
        }

        if ($includePaymentStatus && $request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status')->toString());
        }

        if ($request->filled('submission_status')) {
            $query->where('submission_status', $request->string('submission_status')->toString());
        }

        if ($request->filled('from')) {
            $from = Carbon::parse($request->string('from')->toString())->startOfDay();
            $query->where('updated_at', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::parse($request->string('to')->toString())->endOfDay();
            $query->where('updated_at', '<=', $to);
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();

            $query->where(function (Builder $sub) use ($q): void {
                $sub->where('responsible_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereHas('club', fn (Builder $clubQ) => $clubQ->where('name', 'like', "%{$q}%"));
            });
        }
    }
}
