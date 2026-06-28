<?php

declare(strict_types=1);

namespace App\Modules\Staff\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Modules\Administration\Models\User;
use App\Modules\Staff\Enums\EmploymentType;
use App\Modules\Staff\Enums\StaffStatus;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Shared\Traits\HasIdentity;
use App\Platform\Shared\Traits\HasUuid;
use App\Platform\Shared\Traits\TracksBlame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Employee master record for ALL staff (not only teachers). Created only through
 * Staff Management; department/designation are Master Data; photo + documents
 * are Media references.
 */
class Staff extends Model
{
    use HasIdentity;
    use HasUuid;
    use SoftDeletes;
    use TracksBlame;

    protected $table = 'staff';

    protected $fillable = [
        'school_id', 'user_id', 'identity_id', 'employee_number',
        'name', 'gender_id', 'date_of_birth', 'marital_status', 'blood_group_id',
        'phone', 'email', 'address', 'city', 'state', 'postal_code',
        'department_id', 'designation_id', 'employment_type', 'joining_date',
        'confirmation_date', 'reporting_manager_id', 'is_teaching',
        'status', 'photo_media_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'confirmation_date' => 'date',
            'is_teaching' => 'boolean',
            'status' => StaffStatus::class,
            'employment_type' => EmploymentType::class,
        ];
    }

    public function identityType(): IdentityType
    {
        return IdentityType::Staff;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Department is Master Data (never hardcoded). */
    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'department_id');
    }

    /** Designation is Master Data (never hardcoded). */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'designation_id');
    }

    /** Gender is Master Data (never hardcoded). */
    public function gender(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'gender_id');
    }

    /** Blood group is Master Data (never hardcoded). */
    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'blood_group_id');
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reporting_manager_id');
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(StaffQualification::class)->latest('id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(StaffExperience::class)->latest('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StaffDocument::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(StaffTimeline::class)->latest();
    }
}
