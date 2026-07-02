<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\LibrarySetting;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;

/** Per-school circulation policy. */
class LibrarySettingsService
{
    public function get(int $schoolId): LibrarySetting
    {
        return LibrarySetting::query()->firstOrCreate(['school_id' => $schoolId]);
    }

    /** @param array<string, mixed> $data */
    public function update(int $schoolId, array $data): LibrarySetting
    {
        $settings = $this->get($schoolId);
        $settings->fill($data)->save();

        return $settings->refresh();
    }

    /** Borrow period for an owner (staff vs student). */
    public function borrowDaysFor(Model $owner, int $schoolId): int
    {
        $settings = $this->get($schoolId);

        return $owner instanceof Staff ? $settings->staff_borrow_days : $settings->student_borrow_days;
    }
}
