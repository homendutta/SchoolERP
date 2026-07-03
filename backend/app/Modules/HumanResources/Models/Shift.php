<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable work shift (office hours are never hardcoded). */
class Shift extends Model
{
    use SoftDeletes;

    protected $table = 'hr_shifts';

    protected $fillable = [
        'school_id', 'name', 'code', 'start_time', 'end_time',
        'grace_minutes', 'weekly_off_pattern', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return [
            'grace_minutes' => 'integer',
            'weekly_off_pattern' => 'array',
            'status' => RecordStatus::class,
        ];
    }
}
