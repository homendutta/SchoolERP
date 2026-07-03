<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A downloadable file (prospectus, forms, circulars...). File is a Media reference. */
class Download extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'cms_downloads';

    protected $fillable = [
        'school_id', 'category_id', 'title', 'description', 'media_id', 'downloads_count', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'downloads_count' => 0];

    protected function casts(): array
    {
        return ['downloads_count' => 'integer', 'published_at' => 'datetime', 'status' => ContentStatus::class];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
