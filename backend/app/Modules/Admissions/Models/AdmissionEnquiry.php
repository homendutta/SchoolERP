<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Models;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Administration\Models\MasterDataValue;
use App\Modules\Admissions\Enums\EnquiryStatus;
use App\Platform\Shared\Traits\HasUuid;
use App\Platform\Shared\Traits\TracksBlame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionEnquiry extends Model
{
    use HasUuid;
    use SoftDeletes;
    use TracksBlame;

    protected $fillable = [
        'school_id', 'academic_year_id', 'enquiry_number', 'student_name', 'guardian_name',
        'phone', 'email', 'class_interested', 'source_id', 'status', 'remarks',
        'follow_up_date', 'converted_application_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'follow_up_date' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** Enquiry source is Master Data (never hardcoded). */
    public function source(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'source_id');
    }
}
