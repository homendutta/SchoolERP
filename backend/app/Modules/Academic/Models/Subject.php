<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'subject_type_id', 'code', 'name', 'short_name', 'slug',
        'theory', 'practical', 'credits', 'display_order', 'status',
    ];

    protected function casts(): array
    {
        return [
            'theory' => 'boolean',
            'practical' => 'boolean',
            'credits' => 'integer',
            'display_order' => 'integer',
            'status' => RecordStatus::class,
        ];
    }

    /** Subject type is Master Data (never hardcoded). */
    public function subjectType(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'subject_type_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(SubjectGroup::class, 'subject_group_subjects');
    }
}
