<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\DiscountMethod;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable sibling-concession rule (2nd child, 3rd child…). */
class SiblingDiscountRule extends Model
{
    use SoftDeletes;

    protected $table = 'sibling_discount_rules';

    protected $fillable = ['school_id', 'name', 'child_order', 'method', 'value', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'method' => 'percentage'];

    protected function casts(): array
    {
        return [
            'child_order' => 'integer',
            'method' => DiscountMethod::class,
            'value' => 'float',
            'status' => RecordStatus::class,
        ];
    }
}
