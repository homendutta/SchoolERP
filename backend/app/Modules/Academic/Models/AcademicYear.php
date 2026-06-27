<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Platform\Enums\RecordStatus;
use App\Platform\Shared\Traits\HasUuid;
use App\Platform\Shared\Traits\TracksBlame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasUuid;
    use SoftDeletes;
    use TracksBlame;

    protected $fillable = [
        'school_id', 'name', 'short_name', 'slug', 'start_date', 'end_date',
        'is_current', 'status', 'sort_order', 'version',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'sort_order' => 'integer',
            'version' => 'integer',
            'status' => RecordStatus::class,
        ];
    }

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class)->orderBy('sort_order');
    }

    public function calendars(): HasMany
    {
        return $this->hasMany(AcademicCalendar::class);
    }
}
