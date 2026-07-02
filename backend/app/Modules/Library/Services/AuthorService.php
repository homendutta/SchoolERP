<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\Author;
use App\Platform\Shared\Services\BaseCrudService;

class AuthorService extends BaseCrudService
{
    protected function model(): string
    {
        return Author::class;
    }

    protected function searchable(): array
    {
        return ['name'];
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
