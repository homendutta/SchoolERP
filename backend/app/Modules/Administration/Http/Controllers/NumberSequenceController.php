<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Http\Requests\NumberSequenceRequest;
use App\Modules\Administration\Http\Resources\NumberSequenceResource;
use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Administration\Services\NumberSequenceService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Number Generator administration: configure sequences, preview the next
 * number, reset (permission-protected), and view issue history.
 */
class NumberSequenceController extends BaseController
{
    public function __construct(
        private readonly NumberSequenceService $sequences,
        private readonly NumberGeneratorService $generator,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->sequences->list($request->all());

        return $this->ok(NumberSequenceResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new NumberSequenceResource($this->sequences->find($id)));
    }

    public function update(NumberSequenceRequest $request, int|string $id): JsonResponse
    {
        $model = $this->sequences->find($id);

        return $this->ok(
            new NumberSequenceResource($this->sequences->update($model, $request->validated())),
            'Sequence updated.',
        );
    }

    public function preview(string $key): JsonResponse
    {
        return $this->ok(['key' => $key, 'next' => $this->generator->peek($key)]);
    }

    public function reset(string $key): JsonResponse
    {
        $sequence = $this->generator->reset($key);

        return $this->ok(new NumberSequenceResource($sequence), 'Sequence reset.');
    }

    public function history(string $key): JsonResponse
    {
        return $this->ok($this->generator->history($key));
    }
}
