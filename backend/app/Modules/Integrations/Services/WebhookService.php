<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Integrations\Enums\WebhookDirection;
use App\Modules\Integrations\Models\Webhook;
use App\Platform\Shared\Services\BaseCrudService;

class WebhookService extends BaseCrudService
{
    protected function model(): string
    {
        return Webhook::class;
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'direction', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['direction' => ['type' => 'enum', 'enum' => WebhookDirection::class]];
    }
}
