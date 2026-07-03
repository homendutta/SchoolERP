<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A public event (no ticketing). */
class Event extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'cms_events';

    protected $fillable = [
        'school_id', 'title', 'description', 'event_date', 'start_time', 'end_time',
        'venue', 'featured_image_media_id', 'registration_required', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'registration_required' => false];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'registration_required' => 'boolean',
            'published_at' => 'datetime',
            'status' => ContentStatus::class,
        ];
    }
}
