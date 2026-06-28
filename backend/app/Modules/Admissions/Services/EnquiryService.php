<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Admissions\Enums\EnquiryStatus;
use App\Modules\Admissions\Models\AdmissionEnquiry;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EnquiryService extends BaseCrudService
{
    public function __construct(private readonly NumberGeneratorService $numbers) {}

    protected function model(): string
    {
        return AdmissionEnquiry::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['source:id,label,value', 'academicYear:id,name']);
    }

    protected function searchable(): array
    {
        return ['student_name', 'guardian_name', 'phone', 'email', 'enquiry_number'];
    }

    protected function filterable(): array
    {
        return ['status', 'academic_year_id', 'school_id', 'source_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at', 'follow_up_date', 'student_name'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): AdmissionEnquiry {
            $data['enquiry_number'] ??= $this->numbers->next('admission_enquiry', $data['school_id'] ?? null);
            $data['status'] ??= EnquiryStatus::New->value;

            /** @var AdmissionEnquiry $enquiry */
            $enquiry = AdmissionEnquiry::query()->create($data);

            return $enquiry;
        });
    }
}
