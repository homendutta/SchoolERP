<?php

declare(strict_types=1);

namespace App\Modules\Parents\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Shared\Traits\HasIdentity;
use App\Platform\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Guardian (parent). Created automatically during enrollment — never via a
 * standalone CRUD. Owns an optional Parent login user.
 */
class Guardian extends Model
{
    use HasIdentity;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'user_id', 'identity_id', 'parent_number', 'name', 'relation',
        'phone', 'email', 'occupation', 'address', 'status', 'photo_media_id',
    ];

    public function identityType(): IdentityType
    {
        return IdentityType::Guardian;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot([
                'relationship_type_id', 'is_primary', 'emergency_contact',
                'pickup_authorized', 'financial_responsible', 'notes',
            ])
            ->withTimestamps();
    }
}
