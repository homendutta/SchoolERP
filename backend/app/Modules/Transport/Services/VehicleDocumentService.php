<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Models\VehicleDocument;
use App\Platform\Shared\Services\BaseCrudService;

/** Vehicle documents (Media references only). */
class VehicleDocumentService extends BaseCrudService
{
    protected function model(): string
    {
        return VehicleDocument::class;
    }

    protected function filterable(): array
    {
        return ['school_id', 'vehicle_id', 'document_type'];
    }

    protected function sortable(): array
    {
        return ['id', 'expiry_date'];
    }
}
