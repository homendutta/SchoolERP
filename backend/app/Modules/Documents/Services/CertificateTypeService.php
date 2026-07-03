<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Models\CertificateType;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class CertificateTypeService extends BaseCrudService
{
    protected function model(): string
    {
        return CertificateType::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('category:id,name');
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'category_id', 'subject_kind', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
