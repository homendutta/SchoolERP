<?php

declare(strict_types=1);

namespace App\Modules\Communication\Models;

use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Communication\Enums\AudienceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A school announcement (sent via the Communication Engine). */
class Announcement extends Model
{
    use SoftDeletes;

    protected $table = 'announcements';

    protected $fillable = [
        'school_id', 'title', 'body', 'audience_type',
        'class_id', 'section_id', 'department_id', 'status', 'published_at', 'created_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'published', 'audience_type' => 'school'];

    protected function casts(): array
    {
        return ['audience_type' => AudienceType::class, 'published_at' => 'datetime'];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
