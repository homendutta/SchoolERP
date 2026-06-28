<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Services\DefaulterService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Defaulter lists are generated dynamically. */
class DefaulterController extends BaseController
{
    public function __construct(private readonly DefaulterService $service) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'integer'],
            'class_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'fee_category_id' => ['nullable', 'integer'],
            'as_of' => ['nullable', 'date'],
        ]);

        return $this->ok($this->service->list((int) $validated['school_id'], $validated));
    }
}
