<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Http\Resources\PermissionResource;
use App\Modules\Administration\Http\Resources\RoleResource;
use App\Modules\Administration\Models\Permission;
use App\Modules\Administration\Models\Role;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class RolesController extends BaseController
{
    public function index(): JsonResponse
    {
        $roles = Role::query()->with('permissions')->orderBy('name')->get();

        return $this->ok(RoleResource::collection($roles));
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::query()->orderBy('module')->orderBy('action')->get();

        return $this->ok(PermissionResource::collection($permissions));
    }
}
