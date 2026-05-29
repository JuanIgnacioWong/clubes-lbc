<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\Club;
use App\Models\Season;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $kpis = [
            'total_clubs' => Club::query()->count(),
            'inscripciones' => Submission::query()->count(),
            'pending_payments' => Submission::query()->where('payment_status', Submission::PAYMENT_PENDING)->count(),
            'in_review_payments' => Submission::query()->where('payment_status', Submission::PAYMENT_IN_REVIEW)->count(),
            'paid' => Submission::query()->where('payment_status', Submission::PAYMENT_PAID)->count(),
            'accepted' => SubmissionVersion::query()->where('status', SubmissionVersion::STATUS_ACCEPTED)->count(),
            'rejected' => SubmissionVersion::query()->where('status', SubmissionVersion::STATUS_REJECTED)->count(),
        ];

        $rows = Submission::query()
            ->with(['season', 'division', 'club'])
            ->latest('updated_at')
            ->paginate(20);

        $seasons = Season::query()->orderByDesc('year')->get();

        return view('admin.dashboard', compact('kpis', 'rows', 'seasons'));
    }
}
