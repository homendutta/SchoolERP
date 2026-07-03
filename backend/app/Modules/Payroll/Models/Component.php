<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\CalculationType;
use App\Modules\Payroll\Enums\ComponentType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable salary component (earning / deduction / employer / informational). */
class Component extends Model
{
    use SoftDeletes;

    protected $table = 'payroll_components';

    protected $fillable = [
        'school_id', 'name', 'code', 'component_type', 'calculation_type',
        'default_value', 'based_on', 'formula', 'taxable', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'calculation_type' => 'fixed'];

    protected function casts(): array
    {
        return [
            'default_value' => 'decimal:2',
            'taxable' => 'boolean',
            'component_type' => ComponentType::class,
            'calculation_type' => CalculationType::class,
            'status' => RecordStatus::class,
        ];
    }
}
