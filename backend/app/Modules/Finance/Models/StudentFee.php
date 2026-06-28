<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Finance\Enums\FeePaymentStatus;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A Fee Structure assigned to a student, with computed totals. */
class StudentFee extends Model
{
    use SoftDeletes;

    protected $table = 'student_fees';

    protected $fillable = [
        'school_id', 'student_id', 'fee_structure_id', 'academic_year_id', 'class_id', 'section_id',
        'total_amount', 'discount_amount', 'scholarship_amount', 'net_amount', 'paid_amount', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return [
            'total_amount' => 'float',
            'discount_amount' => 'float',
            'scholarship_amount' => 'float',
            'net_amount' => 'float',
            'paid_amount' => 'float',
            'status' => FeePaymentStatus::class,
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StudentFeeItem::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FeeInstallment::class)->orderBy('sort_order');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }
}
