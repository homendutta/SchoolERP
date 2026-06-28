<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A Fee Structure combines multiple Fee Masters. */
class FeeStructure extends Model
{
    use SoftDeletes;

    protected $table = 'fee_structures';

    protected $fillable = ['school_id', 'academic_year_id', 'class_id', 'name', 'code', 'description', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeStructureItem::class)->orderBy('sort_order');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
