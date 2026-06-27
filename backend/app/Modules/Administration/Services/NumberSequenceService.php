<?php

declare(strict_types=1);

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\NumberSequence;
use App\Platform\Shared\Services\BaseCrudService;

class NumberSequenceService extends BaseCrudService
{
    protected function model(): string
    {
        return NumberSequence::class;
    }

    protected function searchable(): array
    {
        return ['key', 'label', 'prefix'];
    }

    protected function sortable(): array
    {
        return ['id', 'key', 'created_at'];
    }
}
