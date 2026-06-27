<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Http\Resources\UserResource;
use App\Modules\Administration\Models\User;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class UsersController extends BaseController
{
    public function index(): JsonResponse
    {
        $users = User::query()->with('roles')->latest()->paginate(15);

        return $this->ok(UserResource::collection($users), null, 200, [
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ]);
    }
}
