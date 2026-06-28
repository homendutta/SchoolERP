<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\DiscountMethod;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable discount definition (Merit, Sports, Staff Child…). */
class Discount extends Model
{
    use SoftDeletes;

    protected $table = 'discounts';

    protected $fillable = ['school_id', 'name', 'code', 'method', 'value', 'description', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'method' => 'percentage'];

    protected function casts(): array
    {
        return ['method' => DiscountMethod::class, 'value' => 'float', 'status' => RecordStatus::class];
    }
}
