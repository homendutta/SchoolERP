<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Enums\VideoProvider;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A video-gallery item — external (YouTube/Vimeo URL) or self-hosted (Media). */
class Video extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'cms_videos';

    protected $fillable = [
        'school_id', 'category_id', 'title', 'description', 'provider', 'video_url',
        'media_id', 'thumbnail_media_id', 'featured', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'provider' => 'youtube', 'featured' => false];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'published_at' => 'datetime',
            'provider' => VideoProvider::class,
            'status' => ContentStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
