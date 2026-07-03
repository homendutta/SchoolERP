<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Modules\Documents\Http\Requests\TemplateRequest;
use App\Modules\Documents\Http\Resources\SimpleResource;
use App\Modules\Documents\Models\Template;
use App\Modules\Documents\Services\TemplateService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Versioned document templates. */
class TemplateController extends BaseCrudController
{
    public function __construct(private readonly TemplateService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(TemplateRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(TemplateRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Create a new immutable version from an existing template (old version preserved). */
    public function version(Request $request, int|string $id): JsonResponse
    {
        $template = Template::query()->findOrFail($id);
        $changes = $request->validate([
            'html' => ['nullable', 'string'],
            'header' => ['nullable', 'string'],
            'footer' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
            'margins' => ['nullable', 'array'],
            'orientation' => ['nullable', 'string'],
            'paper_size' => ['nullable', 'string'],
        ]);

        return $this->ok(new SimpleResource($this->service->createVersion($template, $changes)), 'New template version created.', 201);
    }
}
