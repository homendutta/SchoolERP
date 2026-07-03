<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Models\GeneratedDocument;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + search over generated documents (history). Documents are immutable. */
class DocumentService extends BaseCrudService
{
    protected function model(): string
    {
        return GeneratedDocument::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('certificateType:id,name');
    }

    protected function filterable(): array
    {
        return ['school_id', 'certificate_type_id', 'subject_type', 'subject_id', 'status', 'version'];
    }

    protected function sortable(): array
    {
        return ['id', 'issue_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'document_number' => ['type' => 'text', 'columns' => ['document_number']],
            'verification_code' => ['type' => 'text', 'columns' => ['verification_code']],
            'status' => ['type' => 'enum', 'enum' => DocumentStatus::class],
            'certificateType' => ['type' => 'relation', 'relation' => 'certificateType', 'columns' => ['name']],
        ];
    }
}
