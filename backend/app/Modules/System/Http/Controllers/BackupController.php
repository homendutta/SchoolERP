<?php

declare(strict_types=1);

namespace App\Modules\System\Http\Controllers;

use App\Modules\System\Enums\BackupType;
use App\Modules\System\Models\Backup;
use App\Modules\System\Services\BackupService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Backup management (metadata + restore metadata; verification). */
class BackupController extends BaseController
{
    public function __construct(private readonly BackupService $service) {}

    public function index(Request $request): JsonResponse
    {
        $rows = Backup::query()->latest('id')->paginate((int) ($request->integer('per_page') ?: 25));

        return $this->ok($rows->items(), null, 200, [
            'total' => $rows->total(), 'per_page' => $rows->perPage(), 'current_page' => $rows->currentPage(), 'last_page' => $rows->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(Backup::query()->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'type' => ['required', Rule::in(BackupType::values())],
            'school_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $backup = $this->service->create(
            BackupType::from($v['type']),
            isset($v['school_id']) ? (int) $v['school_id'] : null,
            $request->user()?->id,
            $v['note'] ?? null,
        );

        return $this->ok($backup, 'Backup manifest recorded.', 201);
    }

    public function verify(int|string $id): JsonResponse
    {
        $backup = Backup::query()->findOrFail($id);

        return $this->ok($this->service->verify($backup), 'Backup verified.');
    }
}
