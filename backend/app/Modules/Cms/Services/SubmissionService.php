<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\EnquiryStatus;
use App\Modules\Cms\Enums\FormType;
use App\Modules\Cms\Models\FormSubmission;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;

/**
 * Contact / general-enquiry form submissions. Captured into the ERP; the
 * Communication Engine notifies administrators (the CMS never emails directly).
 */
class SubmissionService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly CmsHooks $hooks,
    ) {}

    protected function model(): string
    {
        return FormSubmission::class;
    }

    protected function searchable(): array
    {
        return ['name', 'email', 'subject'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'form_id', 'type', 'status'];
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
        return [
            'type' => ['type' => 'enum', 'enum' => FormType::class],
            'status' => ['type' => 'enum', 'enum' => EnquiryStatus::class],
        ];
    }

    /** Capture a public form submission and notify staff via Communication. */
    public function capture(array $data): FormSubmission
    {
        return $this->transaction(function () use ($data): FormSubmission {
            /** @var FormSubmission $submission */
            $submission = FormSubmission::query()->create($data);

            $this->activity->record('cms.contact_submitted', 'Contact form submitted', $submission, [
                'type' => $submission->type->value,
            ], (int) $submission->school_id, 'cms');
            $this->hooks->contactSubmitted((int) $submission->school_id, "New {$submission->type->value} submission from {$submission->name}.");

            return $submission;
        });
    }
}
