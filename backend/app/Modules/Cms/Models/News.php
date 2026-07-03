<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A news article with a featured image, gallery, SEO and scheduled publishing. */
class News extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'cms_news';

    protected $fillable = [
        'school_id', 'category_id', 'title', 'slug', 'body', 'excerpt', 'featured_image_media_id',
        'gallery', 'seo', 'publish_date', 'featured', 'status', 'scheduled_at', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'featured' => false];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'seo' => 'array',
            'publish_date' => 'date',
            'featured' => 'boolean',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'status' => ContentStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
