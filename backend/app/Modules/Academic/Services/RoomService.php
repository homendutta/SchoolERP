<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\Room;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class RoomService extends BaseCrudService
{
    protected function model(): string
    {
        return Room::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('roomType:id,label,value');
    }

    protected function searchable(): array
    {
        return ['code', 'name', 'building'];
    }

    protected function filterable(): array
    {
        return ['room_type_id', 'status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'code', 'name', 'display_order', 'created_at'];
    }
}
