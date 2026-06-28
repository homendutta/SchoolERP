<?php

declare(strict_types=1);

namespace App\Modules\Staff\Services;

use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Models\StaffTimeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * The reusable Staff Timeline. Any module records important employee events here
 * and reads them back newest-first.
 */
class StaffTimelineService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        Staff|int $staff,
        string $eventType,
        string $title,
        ?string $description = null,
        array $metadata = [],
    ): StaffTimeline {
        return StaffTimeline::create([
            'staff_id' => $staff instanceof Staff ? $staff->id : $staff,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'performed_by' => Auth::id(),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }

    /** Timeline for an employee, newest first. */
    public function forStaff(int $staffId): Collection
    {
        return StaffTimeline::query()
            ->where('staff_id', $staffId)
            ->latest('id')
            ->get();
    }
}
