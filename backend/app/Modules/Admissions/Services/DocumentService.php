<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Services;

use App\Modules\Admissions\Models\AdmissionDocument;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class DocumentService extends BaseCrudService
{
    protected function model(): string
    {
        return AdmissionDocument::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['documentType:id,label,value', 'media:id,stored_filename,path,disk']);
    }

    protected function filterable(): array
    {
        return ['application_id', 'document_type_id', 'status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }
}
