<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolAcademicSetting extends Model
{
    protected $table = 'school_academic_settings';

    protected $fillable = [
        'school_id', 'academic_year', 'academic_year_start_month', 'session_label',
    ];

    protected function casts(): array
    {
        return ['academic_year_start_month' => 'integer'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
