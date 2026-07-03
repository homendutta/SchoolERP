<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A photo album. Images are Media references (Media storage is never duplicated). */
class Gallery extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'cms_galleries';

    protected $fillable = [
        'school_id', 'category_id', 'title', 'slug', 'description', 'cover_media_id',
        'featured', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'featured' => false];

    protected function casts(): array
    {
        return ['featured' => 'boolean', 'published_at' => 'datetime', 'status' => ContentStatus::class];
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class, 'gallery_id')->orderBy('sequence');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
