<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Services;

use App\Modules\Admissions\Models\AdmissionWorkflowStep;
use App\Platform\Shared\Services\BaseCrudService;

/**
 * CRUD for the configurable approval workflow DEFINITION (Reception → … →
 * Approved). Schools build one-step or multi-step flows; nothing is hardcoded.
 */
class WorkflowStepService extends BaseCrudService
{
    protected function model(): string
    {
        return AdmissionWorkflowStep::class;
    }

    protected function filterable(): array
    {
        return ['school_id', 'is_active'];
    }

    protected function sortable(): array
    {
        return ['id', 'sort_order', 'created_at'];
    }
}
