<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HolidayType extends Model
{
    use SoftDeletes;

    protected $fillable = ['school_id', 'name', 'slug', 'color', 'status'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }
}
