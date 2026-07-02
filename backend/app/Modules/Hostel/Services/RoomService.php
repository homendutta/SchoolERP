<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Enums\BedStatus;
use App\Modules\Hostel\Models\Room;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Hostel rooms. Room type is Master Data; capacity is enforced when adding beds. */
class RoomService extends BaseCrudService
{
    protected function model(): string
    {
        return Room::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['hostel:id,name', 'building:id,name', 'roomType:id,label'])->withCount('beds');
    }

    protected function searchable(): array
    {
        return ['room_number'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'hostel_id', 'building_id', 'floor_id', 'room_type_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'room_number'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['status' => ['type' => 'enum', 'enum' => BedStatus::class]];
    }
}
