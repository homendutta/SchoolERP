<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Resources;

use App\Modules\Administration\Models\User;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin User
 */
class UserResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'staff_number' => $this->staff_number,
            'phone' => $this->phone,
            'status' => $this->status,
            'is_super_admin' => $this->isSuperAdmin(),
            'must_change_password' => (bool) $this->must_change_password,
            'school_id' => $this->school_id,
            'roles' => $this->roles->pluck('slug'),
            'permissions' => $this->permissionSlugs(),
            'last_login_at' => optional($this->last_login_at)->toIso8601String(),
        ];
    }
}
