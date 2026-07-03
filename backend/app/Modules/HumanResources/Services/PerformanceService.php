<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Enums\ReviewStatus;
use App\Modules\HumanResources\Models\PerformanceReview;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Performance reviews. Stored as history; scheduling publishes a Communication event. */
class PerformanceService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
        private readonly HrHooks $hooks,
    ) {}

    protected function model(): string
    {
        return PerformanceReview::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number', 'reviewer:id,name,employee_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'reviewer_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'review_period_end', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => ReviewStatus::class],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $review = PerformanceReview::query()->create($data);

            $this->timeline->record((int) $review->staff_id, 'hr.review_scheduled', 'Performance review scheduled', null, [
                'review_id' => $review->id,
            ]);
            $this->activity->record('hr.review_scheduled', 'Performance review scheduled', $review, [], (int) $review->school_id, 'hr');
            $this->hooks->reviewScheduled((int) $review->school_id, "Performance review scheduled for employee #{$review->staff_id}.");

            return $review->refresh();
        });
    }
}
