<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Enums\CopyStatus;
use App\Modules\Library\Enums\InventoryStatus;
use App\Modules\Library\Models\Copy;
use App\Modules\Library\Models\InventoryCheck;
use App\Platform\Foundation\Audit\ActivityLogger;
use Illuminate\Support\Carbon;

/**
 * Inventory verification. Records the audited state of a copy (verified /
 * missing / misplaced / damaged) and can report the outcome. Copies are never
 * deleted; a missing/damaged result updates status but keeps full history.
 */
class InventoryService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function record(Copy $copy, InventoryStatus $status, ?string $notes, ?int $checkedBy): InventoryCheck
    {
        $check = InventoryCheck::query()->create([
            'school_id' => $copy->school_id,
            'copy_id' => $copy->id,
            'status' => $status->value,
            'notes' => $notes,
            'checked_at' => Carbon::now(),
            'checked_by' => $checkedBy,
        ]);

        // A missing or damaged audit reflects onto the copy (history preserved).
        if ($status === InventoryStatus::Missing) {
            $copy->update(['status' => CopyStatus::Lost->value]);
        } elseif ($status === InventoryStatus::Damaged) {
            $copy->update(['status' => CopyStatus::Damaged->value]);
        }

        $this->activity->record('library.inventory_checked', "Copy {$copy->copy_number}: {$status->value}", $check, [], $copy->school_id, 'library');

        return $check;
    }

    /**
     * @return array<string, int>
     */
    public function report(int $schoolId): array
    {
        $latest = InventoryCheck::query()
            ->where('school_id', $schoolId)
            ->get(['copy_id', 'status', 'id'])
            ->groupBy('copy_id')
            ->map(fn ($g) => $g->sortByDesc('id')->first()->status->value);

        $counts = ['verified' => 0, 'missing' => 0, 'misplaced' => 0, 'damaged' => 0];
        foreach ($latest as $status) {
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        return $counts;
    }
}
