<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Vendor;
use App\Platform\Shared\Services\BaseCrudService;

class VendorService extends BaseCrudService
{
    protected function model(): string
    {
        return Vendor::class;
    }

    protected function searchable(): array
    {
        return ['name', 'contact', 'gst_number'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }
}
