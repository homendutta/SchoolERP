<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolRegional extends Model
{
    protected $table = 'school_regional';

    protected $fillable = [
        'school_id', 'timezone', 'currency', 'locale',
        'date_format', 'time_format', 'week_start',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
