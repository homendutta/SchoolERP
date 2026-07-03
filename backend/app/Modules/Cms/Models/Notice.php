<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Enums\NoticePriority;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A notice-board item. The public site shows current (published, in-window) notices. */
class Notice extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'cms_notices';

    protected $fillable = [
        'school_id', 'category_id', 'title', 'body', 'publish_date', 'expiry_date',
        'priority', 'featured', 'attachment_media_id', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'priority' => 'normal', 'featured' => false];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'expiry_date' => 'date',
            'featured' => 'boolean',
            'published_at' => 'datetime',
            'priority' => NoticePriority::class,
            'status' => ContentStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
