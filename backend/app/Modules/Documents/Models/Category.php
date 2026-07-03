<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A configurable document category (Student, Staff, Academic, Finance, ...). */
class Category extends Model
{
    use SoftDeletes;

    protected $table = 'document_categories';

    protected $fillable = ['school_id', 'name', 'code', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }
}
