<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Modules\Documents\Http\Resources\SimpleResource;
use App\Modules\Documents\Models\GeneratedDocument;
use App\Modules\Documents\Models\Template;
use App\Modules\Documents\Services\BulkGenerationService;
use App\Modules\Documents\Services\DocumentService;
use App\Modules\Documents\Services\GenerationService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Document generation: preview, generate (immutable), regenerate (new version), bulk (queued), history. */
class GenerationController extends BaseController
{
    public function __construct(
        private readonly GenerationService $generation,
        private readonly BulkGenerationService $bulk,
        private readonly DocumentService $documents,
    ) {}

    public function preview(Request $request): JsonResponse
    {
        $v = $request->validate([
            'template_id' => ['required', 'integer', 'exists:document_templates,id'],
            'subject_kind' => ['required', 'in:student,staff,guardian'],
            'subject_id' => ['nullable', 'integer'],
        ]);
        $template = Template::query()->findOrFail($v['template_id']);

        return $this->ok($this->generation->preview($template, $v['subject_kind'], isset($v['subject_id']) ? (int) $v['subject_id'] : null));
    }

    public function generate(Request $request): JsonResponse
    {
        $v = $request->validate([
            'template_id' => ['required', 'integer', 'exists:document_templates,id'],
            'subject_kind' => ['required', 'in:student,staff,guardian'],
            'subject_id' => ['required', 'integer'],
            'certificate_type_id' => ['nullable', 'integer'],
            'issued_to' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            'signatures' => ['nullable', 'array'],
        ]);
        $template = Template::query()->findOrFail($v['template_id']);

        return $this->ok(new SimpleResource($this->generation->generate($template, $v['subject_kind'], (int) $v['subject_id'], $v)), 'Document generated.', 201);
    }

    public function regenerate(int|string $id): JsonResponse
    {
        $document = GeneratedDocument::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->generation->regenerate($document)), 'Document regenerated as a new version.', 201);
    }

    public function bulk(Request $request): JsonResponse
    {
        $v = $request->validate([
            'template_id' => ['required', 'integer', 'exists:document_templates,id'],
            'subject_kind' => ['required', 'in:student,staff,guardian'],
            'scope' => ['required', 'in:class,section,examination,academic_year,department'],
            'target' => ['required', 'array'],
        ]);
        $template = Template::query()->findOrFail($v['template_id']);

        return $this->ok($this->bulk->generate($template, $v['scope'], $v['target'], $v['subject_kind']), 'Bulk generation queued.', 202);
    }

    public function history(Request $request): JsonResponse
    {
        $page = $this->documents->list($request->all());

        return $this->ok(SimpleResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new SimpleResource($this->documents->find($id)));
    }
}
