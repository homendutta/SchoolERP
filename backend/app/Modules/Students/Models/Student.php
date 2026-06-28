<?php

declare(strict_types=1);

namespace App\Modules\Students\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Modules\Administration\Models\User;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Enums\StudentStatus;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Shared\Traits\HasIdentity;
use App\Platform\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Student identity master. Created ONLY through an approved admission (or a
 * migration import) — never via manual CRUD. Per-year placement lives in
 * StudentAcademicRecord so a promotion never overwrites history.
 */
class Student extends Model
{
    use HasIdentity;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'user_id', 'identity_id', 'admission_application_id', 'admission_number',
        'name', 'phone', 'email', 'gender', 'date_of_birth',
        'blood_group', 'blood_group_id', 'religion', 'nationality', 'category',
        'address', 'city', 'state', 'postal_code',
        'allergies', 'disabilities', 'medical_notes', 'emergency_instructions',
        'notes', 'status', 'enrolled_on', 'photo_media_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'enrolled_on' => 'date',
            'status' => StudentStatus::class,
        ];
    }

    public function identityType(): IdentityType
    {
        return IdentityType::Student;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Blood group is Master Data (never hardcoded). */
    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'blood_group_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot([
                'relationship_type_id', 'is_primary', 'emergency_contact',
                'pickup_authorized', 'financial_responsible', 'notes',
            ])
            ->withTimestamps();
    }

    public function academicRecords(): HasMany
    {
        return $this->hasMany(StudentAcademicRecord::class)->orderByDesc('id');
    }

    /**
     * The current placement = the most recent academic record. History is
     * immutable: promotion/transfer ADD a record, so "current" is always derived
     * from the latest one (never from a flag mutated on old records).
     */
    public function currentRecord(): HasOne
    {
        return $this->hasOne(StudentAcademicRecord::class)->latestOfMany();
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(StudentTimeline::class)->latest();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StudentTransfer::class)->latest();
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(StudentWithdrawal::class)->latest();
    }
}
