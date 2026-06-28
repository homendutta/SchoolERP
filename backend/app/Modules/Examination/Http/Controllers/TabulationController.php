<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Services\TabulationService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TabulationController extends BaseController
{
    public function __construct(private readonly TabulationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_session_id' => ['required', 'integer'],
            'class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
        ]);

        return $this->ok($this->service->build(
            (int) $validated['exam_session_id'],
            (int) $validated['class_id'],
            isset($validated['section_id']) ? (int) $validated['section_id'] : null,
        ));
    }
}
