<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Controllers;

use App\Modules\Cms\Enums\EnquiryStatus;
use App\Modules\Cms\Http\Resources\SimpleResource;
use App\Modules\Cms\Models\Enquiry;
use App\Modules\Cms\Services\EnquiryService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin view of admission enquiries (read + status). Public capture is elsewhere. */
class EnquiryController extends BaseController
{
    public function __construct(private readonly EnquiryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(SimpleResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new SimpleResource($this->service->find($id)));
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(EnquiryStatus::values())],
            'notes' => ['nullable', 'string'],
        ]);

        $enquiry = Enquiry::query()->findOrFail($id);
        $enquiry->fill($validated)->save();

        return $this->ok(new SimpleResource($enquiry->refresh()), 'Enquiry updated.');
    }
}
