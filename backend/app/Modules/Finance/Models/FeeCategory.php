<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable fee category (Tuition, Transport…). */
class FeeCategory extends Model
{
    use SoftDeletes;

    protected $table = 'fee_categories';

    protected $fillable = ['school_id', 'name', 'code', 'description', 'sort_order', 'is_active', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'is_active' => true];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean', 'status' => RecordStatus::class];
    }
}
