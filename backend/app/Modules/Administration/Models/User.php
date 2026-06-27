<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use App\Modules\Administration\Models\Concerns\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User — the identity master record (owned by the Administration module).
 * Authentication (login/session) is handled by the Authentication module.
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'name', 'email', 'username', 'staff_number', 'phone',
        'password', 'status', 'is_super_admin', 'must_change_password', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->deleted_at === null;
    }
}
