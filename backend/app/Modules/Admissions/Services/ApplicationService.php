<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Admissions\Enums\ApplicationStatus;
use App\Modules\Admissions\Enums\EnquiryStatus;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Models\AdmissionEnquiry;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApplicationService extends BaseCrudService
{
    public function __construct(private readonly NumberGeneratorService $numbers) {}

    protected function model(): string
    {
        return AdmissionApplication::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'academicYear:id,name',
            'schoolClass:id,name',
            'section:id,name',
            'documents',
        ]);
    }

    protected function searchable(): array
    {
        return ['student_name', 'guardian_name', 'application_number', 'guardian_phone', 'guardian_email'];
    }

    protected function filterable(): array
    {
        return ['status', 'verification_status', 'academic_year_id', 'class_id', 'section_id', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at', 'student_name', 'submitted_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): AdmissionApplication {
            $data['application_number'] ??= $this->numbers->next('admission_application', $data['school_id'] ?? null);
            $data['status'] ??= ApplicationStatus::Draft->value;

            /** @var AdmissionApplication $application */
            $application = AdmissionApplication::query()->create($data);

            // If this application originated from an enquiry, mark it converted.
            if (! empty($data['enquiry_id'])) {
                AdmissionEnquiry::query()->whereKey($data['enquiry_id'])->update([
                    'status' => EnquiryStatus::Converted->value,
                    'converted_application_id' => $application->id,
                ]);
            }

            return $application;
        });
    }

    /** Move an application from Draft to Submitted (ready for verification). */
    public function submit(AdmissionApplication $application): AdmissionApplication
    {
        if ($application->status === ApplicationStatus::Enrolled) {
            throw BusinessRuleException::make('An enrolled application cannot be resubmitted.', 'ALREADY_ENROLLED');
        }

        $application->forceFill([
            'status' => ApplicationStatus::Submitted->value,
            'submitted_at' => now(),
        ])->save();

        return $application->refresh();
    }
}
