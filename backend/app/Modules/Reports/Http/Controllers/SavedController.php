<?php

declare(strict_types=1);

namespace App\Modules\Reports\Http\Controllers;

use App\Modules\Reports\Http\Resources\SimpleResource;
use App\Modules\Reports\Services\SavedReportService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedController extends BaseCrudController
{
    public function __construct(private readonly SavedReportService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request): array
    {
        $required = $request->isMethod('post') ? 'required' : 'sometimes';

        return [
            'school_id' => [$required, 'integer', 'exists:schools,id'],
            'report_key' => [$required, 'string', 'max:100'],
            'name' => [$required, 'string', 'max:255'],
            'filters' => ['nullable', 'array'],
            'columns' => ['nullable', 'array'],
            'sort' => ['nullable', 'array'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules($request));
        $data['user_id'] = $request->user()->id;

        return $this->created($data);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validate($this->rules($request)));
    }
}
