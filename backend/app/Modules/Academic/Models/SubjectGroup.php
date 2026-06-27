<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectGroup extends Model
{
    use SoftDeletes;

    protected $fillable = ['school_id', 'code', 'name', 'slug', 'display_order', 'status'];

    protected function casts(): array
    {
        return ['display_order' => 'integer', 'status' => RecordStatus::class];
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_group_subjects')->withPivot('sort_order')->withTimestamps();
    }

    public function syncSubjects(array $subjectIds): void
    {
        $this->subjects()->sync($subjectIds);
    }
}
