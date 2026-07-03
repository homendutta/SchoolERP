<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\HolidayType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable holiday (national / state / school / optional). */
class Holiday extends Model
{
    use SoftDeletes;

    protected $table = 'hr_holidays';

    protected $fillable = [
        'school_id', 'name', 'date', 'end_date', 'holiday_type', 'is_optional', 'description', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'holiday_type' => 'school'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'end_date' => 'date',
            'is_optional' => 'boolean',
            'holiday_type' => HolidayType::class,
            'status' => RecordStatus::class,
        ];
    }
}
