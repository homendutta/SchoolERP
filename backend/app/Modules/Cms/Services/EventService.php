<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Models\Event;
use Illuminate\Database\Eloquent\Model;

class EventService extends ContentService
{
    protected function model(): string
    {
        return Event::class;
    }

    protected function contentType(): string
    {
        return 'event';
    }

    protected function searchable(): array
    {
        return ['title', 'venue'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status', 'registration_required'];
    }

    protected function sortable(): array
    {
        return ['id', 'event_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'title' => ['type' => 'text', 'columns' => ['title']],
            'status' => ['type' => 'enum', 'enum' => ContentStatus::class],
        ];
    }

    protected function onPublished(Model $model): void
    {
        $this->hooks->eventPublished((int) $model->getAttribute('school_id'), 'Event: '.$model->getAttribute('title'));
    }
}
