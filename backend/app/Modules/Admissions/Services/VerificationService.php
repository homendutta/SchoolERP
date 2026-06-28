<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Services;

use App\Modules\Admissions\Enums\ApplicationStatus;
use App\Modules\Admissions\Enums\VerificationStatus;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Models\AdmissionDocument;
use App\Modules\Admissions\Models\AdmissionVerificationLog;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Admission verification workflow. Moves an application (or one of its
 * documents) between Pending / Verified / Rejected / On Hold, preserving the
 * full state-change history in admission_verification_logs.
 */
class VerificationService extends BaseService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function verifyApplication(
        AdmissionApplication $application,
        VerificationStatus $to,
        ?string $remarks = null,
    ): AdmissionApplication {
        return $this->transaction(function () use ($application, $to, $remarks): AdmissionApplication {
            $from = $application->verification_status;

            $this->logHistory($application, null, $from, $to, $remarks);

            $application->forceFill(['verification_status' => $to->value]);
            if ($to === VerificationStatus::Verified && $application->status === ApplicationStatus::Submitted) {
                $application->forceFill(['status' => ApplicationStatus::UnderReview->value]);
            }
            $application->save();

            $this->activity->record(
                'admission.verification',
                "Application {$application->application_number} verification → {$to->label()}",
                $application,
                ['from' => $from?->value, 'to' => $to->value],
                $application->school_id,
                'admissions',
            );

            return $application->refresh();
        });
    }

    public function verifyDocument(
        AdmissionDocument $document,
        VerificationStatus $to,
        ?string $remarks = null,
    ): AdmissionDocument {
        return $this->transaction(function () use ($document, $to, $remarks): AdmissionDocument {
            $from = $document->status;

            $this->logHistory(
                $document->application_id,
                $document->id,
                $from,
                $to,
                $remarks,
                $document->school_id,
            );

            $document->forceFill([
                'status' => $to->value,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ])->save();

            return $document->refresh();
        });
    }

    /** Verification history for an application (most recent first). */
    public function history(int $applicationId): Collection
    {
        return AdmissionVerificationLog::query()
            ->where('application_id', $applicationId)
            ->latest('created_at')
            ->get();
    }

    private function logHistory(
        AdmissionApplication|int $application,
        ?int $documentId,
        ?VerificationStatus $from,
        VerificationStatus $to,
        ?string $remarks,
        ?int $schoolId = null,
    ): void {
        $applicationId = $application instanceof AdmissionApplication ? $application->id : $application;
        $schoolId ??= $application instanceof AdmissionApplication ? $application->school_id : null;

        AdmissionVerificationLog::create([
            'school_id' => $schoolId,
            'application_id' => $applicationId,
            'document_id' => $documentId,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'remarks' => $remarks,
            'actor_id' => Auth::id(),
            'created_at' => now(),
        ]);
    }
}
