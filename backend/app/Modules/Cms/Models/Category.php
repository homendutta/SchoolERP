<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\CategoryType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A content category shared across notices/news/gallery/videos/downloads. */
class Category extends Model
{
    use SoftDeletes;

    protected $table = 'cms_categories';

    protected $fillable = ['school_id', 'type', 'name', 'slug', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['type' => CategoryType::class, 'status' => RecordStatus::class];
    }
}
