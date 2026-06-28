<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\DiscountMethod;
use App\Modules\Finance\Enums\ScholarshipType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable scholarship definition (independent of discounts). */
class Scholarship extends Model
{
    use SoftDeletes;

    protected $table = 'scholarships';

    protected $fillable = ['school_id', 'name', 'code', 'type', 'method', 'value', 'description', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'type' => 'partial', 'method' => 'percentage'];

    protected function casts(): array
    {
        return [
            'type' => ScholarshipType::class,
            'method' => DiscountMethod::class,
            'value' => 'float',
            'status' => RecordStatus::class,
        ];
    }
}
