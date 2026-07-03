<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A CMS page (formerly-static page made dynamic) with per-page SEO. */
class Page extends Model
{
    use SoftDeletes;

    protected $table = 'cms_pages';

    protected $fillable = [
        'school_id', 'title', 'slug', 'body', 'template', 'seo', 'status', 'published_at', 'published_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return ['seo' => 'array', 'published_at' => 'datetime', 'status' => ContentStatus::class];
    }
}
