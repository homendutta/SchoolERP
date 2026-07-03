<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\EnquiryStatus;
use App\Modules\Cms\Models\Enquiry;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Model;

/**
 * Admission enquiries. Public submissions capture an enquiry only — they NEVER
 * auto-create an admission (Admissions owns that). Every enquiry is audited and
 * notified via the Communication Engine.
 */
class EnquiryService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly CmsHooks $hooks,
    ) {}

    protected function model(): string
    {
        return Enquiry::class;
    }

    protected function searchable(): array
    {
        return ['parent_name', 'student_name', 'phone', 'email'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status', 'interested_class'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['status' => ['type' => 'enum', 'enum' => EnquiryStatus::class]];
    }

    /** Capture a public admission enquiry (enquiry only; no admission is created). */
    public function capture(array $data): Enquiry
    {
        return $this->transaction(function () use ($data): Enquiry {
            /** @var Enquiry $enquiry */
            $enquiry = Enquiry::query()->create($data);

            $this->activity->record('cms.enquiry_submitted', 'Admission enquiry received', $enquiry, [
                'interested_class' => $enquiry->interested_class,
            ], (int) $enquiry->school_id, 'cms');
            $this->hooks->enquirySubmitted((int) $enquiry->school_id, "Admission enquiry from {$enquiry->parent_name} for {$enquiry->interested_class}.");

            return $enquiry;
        });
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->capture($data);
    }
}
