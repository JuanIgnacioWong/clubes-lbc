<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionService
{
    /**
     * @param array<string,mixed> $payload
     */
    public function createVersion(Season $season, Division $division, Club $club, array $payload): array
    {
        return DB::transaction(function () use ($season, $division, $club, $payload) {
            $submission = Submission::query()->firstOrNew([
                'season_id' => $season->id,
                'division_id' => $division->id,
                'club_id' => $club->id,
            ]);

            if (! $submission->exists) {
                $submission->fill([
                    'payment_status' => Submission::PAYMENT_PENDING,
                    'submission_status' => Submission::STATUS_RECEIVED,
                    'max_allowed_submissions' => 2,
                ]);
            }

            $currentVersions = (int) $submission->versions()->count();
            $maxAllowed = (int) ($submission->max_allowed_submissions ?: 2);

            if ($submission->exists && $currentVersions >= $maxAllowed) {
                throw ValidationException::withMessages([
                    'club_id' => 'El club ya alcanzó el máximo de envíos permitidos.',
                ]);
            }

            $submission->fill([
                'responsible_name' => $payload['responsible_name'],
                'phone' => $payload['phone'],
                'email' => $payload['email'],
                'submission_status' => Submission::STATUS_RECEIVED,
            ]);
            $submission->save();

            $versionNumber = $currentVersions + 1;
            $basePath = sprintf(
                'submissions/%s/%s/%s/version-%d',
                $season->slug,
                $division->slug,
                $club->slug,
                $versionNumber,
            );

            $logoPath = $this->storeFile($payload['club_logo'] ?? null, $basePath, 'logo');
            $receiptPath = $this->storeFile($payload['payment_receipt'] ?? null, $basePath, 'comprobante');
            $rosterPath = $this->storeFile($payload['players_roster'] ?? null, $basePath, 'nomina');

            $version = SubmissionVersion::query()->create([
                'submission_id' => $submission->id,
                'version_number' => $versionNumber,
                'club_logo_path' => $logoPath,
                'payment_receipt_path' => $receiptPath,
                'players_roster_path' => $rosterPath,
                'observations' => $payload['observations'] ?? null,
                'status' => SubmissionVersion::STATUS_RECEIVED,
                'submitted_at' => now(),
            ]);

            if ($submission->active_version === null) {
                $submission->active_version = $version->id;
            }

            if ($receiptPath !== null) {
                $submission->payment_status = Submission::PAYMENT_IN_REVIEW;
            }

            $submission->save();

            return [$submission, $version];
        });
    }

    private function storeFile(?UploadedFile $file, string $basePath, string $prefix): ?string
    {
        if ($file === null) {
            return null;
        }

        $filename = sprintf(
            '%s-%s.%s',
            $prefix,
            Str::random(10),
            $file->getClientOriginalExtension(),
        );

        return Storage::disk('private')->putFileAs($basePath, $file, $filename);
    }
}
