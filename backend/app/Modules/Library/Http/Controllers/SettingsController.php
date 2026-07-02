<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Resources\SimpleResource;
use App\Modules\Library\Services\LibrarySettingsService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Per-school circulation policy (borrow periods, renewals, reservation expiry). */
class SettingsController extends BaseController
{
    public function __construct(private readonly LibrarySettingsService $service) {}

    public function show(Request $request): JsonResponse
    {
        return $this->ok(new SimpleResource($this->service->get((int) $request->integer('school_id'))));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'student_borrow_days' => ['sometimes', 'integer', 'min:1'],
            'staff_borrow_days' => ['sometimes', 'integer', 'min:1'],
            'max_renewals' => ['sometimes', 'integer', 'min:0'],
            'max_books_per_borrower' => ['sometimes', 'integer', 'min:1'],
            'reservation_expiry_days' => ['sometimes', 'integer', 'min:1'],
        ]);

        return $this->ok(new SimpleResource($this->service->update((int) $data['school_id'], $data)), 'Settings saved.');
    }
}
