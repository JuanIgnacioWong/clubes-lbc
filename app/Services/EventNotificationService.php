<?php

namespace App\Services;

use App\Mail\PlatformEventMail;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use Illuminate\Support\Facades\Mail;

class EventNotificationService
{
    public function submissionReceived(Submission $submission, SubmissionVersion $version, string $source): void
    {
        $lines = [
            'Se recibió un nuevo envío de antecedentes.',
            'Temporada: '.$submission->season->year,
            'División: '.$submission->division->name,
            'Club: '.$submission->club->name,
            'Responsable: '.$submission->responsible_name,
            'Versión: '.$version->version_number,
            'Origen: '.($source === 'correction' ? 'Corrección' : 'Inscripción pública'),
        ];

        $this->sendToAdmin(
            subject: 'Nuevo envío recibido - '.$submission->club->name,
            title: 'Nuevo envío de antecedentes',
            lines: $lines,
            actionUrl: route('admin.submissions.show', $submission),
            actionText: 'Revisar en admin',
        );
    }

    public function paymentStatusChanged(Submission $submission, string $oldStatus, string $newStatus): void
    {
        $lines = [
            'Se actualizó el estado de pago de una inscripción.',
            'Club: '.$submission->club->name,
            'Temporada: '.$submission->season->year,
            'División: '.$submission->division->name,
            'Estado anterior: '.$oldStatus,
            'Estado nuevo: '.$newStatus,
        ];

        $this->sendToAdminAndSubmitter(
            $submission,
            subject: 'Cambio de estado de pago - '.$submission->club->name,
            title: 'Actualización de estado de pago',
            lines: $lines,
        );
    }

    public function versionStatusChanged(Submission $submission, SubmissionVersion $version, string $oldStatus, string $newStatus): void
    {
        $lines = [
            'Se actualizó el estado de una versión de antecedentes.',
            'Club: '.$submission->club->name,
            'Temporada: '.$submission->season->year,
            'División: '.$submission->division->name,
            'Versión: '.$version->version_number,
            'Estado anterior: '.$oldStatus,
            'Estado nuevo: '.$newStatus,
        ];

        $this->sendToAdminAndSubmitter(
            $submission,
            subject: 'Cambio de estado de revisión - '.$submission->club->name,
            title: 'Actualización de revisión de antecedentes',
            lines: $lines,
        );
    }

    /**
     * @param array<int,string> $lines
     */
    private function sendToAdmin(string $subject, string $title, array $lines, ?string $actionUrl = null, ?string $actionText = null): void
    {
        $adminEmail = Setting::getValue('notification_email');

        if (! $this->isValidEmail($adminEmail)) {
            return;
        }

        try {
            Mail::to($adminEmail)->send(new PlatformEventMail($subject, $title, $lines, $actionUrl, $actionText));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * @param array<int,string> $lines
     */
    private function sendToAdminAndSubmitter(Submission $submission, string $subject, string $title, array $lines): void
    {
        $recipients = [];
        $adminEmail = Setting::getValue('notification_email');

        if ($this->isValidEmail($adminEmail)) {
            $recipients[] = $adminEmail;
        }

        if ($this->isValidEmail($submission->email) && ! in_array($submission->email, $recipients, true)) {
            $recipients[] = $submission->email;
        }

        if (empty($recipients)) {
            return;
        }

        try {
            Mail::to($recipients)->send(new PlatformEventMail(
                $subject,
                $title,
                $lines,
                route('public.inscripciones'),
                'Ir a la plataforma'
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function isValidEmail(?string $email): bool
    {
        if ($email === null || $email === '') {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
