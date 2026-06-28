<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationTemplate;
use App\Platform\Shared\Services\BaseCrudService;

class TemplateService extends BaseCrudService
{
    protected function model(): string
    {
        return CommunicationTemplate::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code', 'subject'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'channel', 'language', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['channel' => ['type' => 'enum', 'enum' => CommunicationChannel::class]];
    }
}
