<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** A post in a discussion (author is a Student or a teaching User). */
class DiscussionPost extends Model
{
    protected $table = 'lms_discussion_posts';

    protected $fillable = ['school_id', 'discussion_id', 'author_type', 'author_id', 'body', 'parent_id', 'status'];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class, 'discussion_id');
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }
}
