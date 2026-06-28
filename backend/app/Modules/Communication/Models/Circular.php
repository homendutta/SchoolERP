<?php

declare(strict_types=1);

namespace App\Modules\Communication\Models;

use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Communication\Enums\AudienceType;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A circular with an optional Media attachment (never a file path). */
class Circular extends Model
{
    use SoftDeletes;

    protected $table = 'circulars';

    protected $fillable = [
        'school_id', 'title', 'body', 'media_id', 'audience_type',
        'class_id', 'section_id', 'department_id', 'publish_date', 'expiry_date', 'status', 'created_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'published', 'audience_type' => 'school'];

    protected function casts(): array
    {
        return [
            'audience_type' => AudienceType::class,
            'publish_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    /** Attachment is a Media Platform reference. */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
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
