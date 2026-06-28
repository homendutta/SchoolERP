<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Enums\AttendanceSource;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read + search over the unified attendance table. Reused by the student and
 * staff attendance endpoints (the controller scopes owner_type).
 */
class AttendanceRecordService extends BaseCrudService
{
    protected function model(): string
    {
        return AttendanceRecord::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'identity:id,identity_number,public_identifier',
            'owner',
            'session:id,label,value',
        ]);
    }

    protected function filterable(): array
    {
        return [
            'status', 'source', 'owner_type', 'school_id', 'session_id',
            'class_id', 'section_id', 'academic_year_id', 'department_id', 'designation_id',
        ];
    }

    protected function sortable(): array
    {
        return ['id', 'attendance_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'identity_number' => ['type' => 'relation', 'relation' => 'identity', 'columns' => ['identity_number']],
            'owner' => ['type' => 'relation', 'relation' => 'owner', 'columns' => ['name']],
            'attendance_date' => ['type' => 'date'],
            'status' => ['type' => 'enum', 'enum' => AttendanceStatus::class],
            'source' => ['type' => 'enum', 'enum' => AttendanceSource::class],
        ];
    }
}
