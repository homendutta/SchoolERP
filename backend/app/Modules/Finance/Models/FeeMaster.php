<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Finance\Enums\FeeFrequency;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A reusable Fee Master — what is owed. Payments never modify it. */
class FeeMaster extends Model
{
    use SoftDeletes;

    protected $table = 'fee_masters';

    protected $fillable = [
        'school_id', 'fee_category_id', 'academic_year_id', 'class_id',
        'name', 'amount', 'due_date', 'frequency', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'frequency' => 'one_time'];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'due_date' => 'date',
            'frequency' => FeeFrequency::class,
            'status' => RecordStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
