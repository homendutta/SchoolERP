<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Models;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Admissions\Enums\ApplicationStatus;
use App\Modules\Admissions\Enums\VerificationStatus;
use App\Platform\Shared\Traits\HasUuid;
use App\Platform\Shared\Traits\TracksBlame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionApplication extends Model
{
    use HasUuid;
    use SoftDeletes;
    use TracksBlame;

    protected $fillable = [
        'school_id', 'academic_year_id', 'class_id', 'section_id', 'enquiry_id', 'application_number',
        'student_name', 'gender', 'date_of_birth', 'blood_group', 'nationality', 'religion',
        'guardian_name', 'guardian_relation', 'guardian_phone', 'guardian_email', 'guardian_occupation',
        'address', 'city', 'state', 'postal_code', 'previous_school', 'previous_class',
        'remarks', 'status', 'verification_status', 'submitted_at', 'approved_at', 'enrolled_student_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'status' => ApplicationStatus::class,
            'verification_status' => VerificationStatus::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AdmissionDocument::class, 'application_id');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(AdmissionApprovalStep::class, 'application_id')->orderBy('sort_order');
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(AdmissionVerificationLog::class, 'application_id')->latest();
    }
}
