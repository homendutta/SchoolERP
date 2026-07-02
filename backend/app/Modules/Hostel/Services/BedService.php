<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Hostel\Enums\BedStatus;
use App\Modules\Hostel\Models\Bed;
use App\Modules\Hostel\Models\Room;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Beds. Bed code from the Number Generator. Room capacity is ENFORCED — a room
 * can never hold more beds than its capacity.
 */
class BedService extends BaseCrudService
{
    public function __construct(private readonly NumberGeneratorService $numbers) {}

    protected function model(): string
    {
        return Bed::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['room:id,room_number,capacity']);
    }

    protected function searchable(): array
    {
        return ['bed_number', 'bed_code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'room_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'bed_number'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'bed_code' => ['type' => 'text', 'columns' => ['bed_code', 'bed_number']],
            'status' => ['type' => 'enum', 'enum' => BedStatus::class],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $room = Room::query()->findOrFail($data['room_id']);
            if (Bed::query()->where('room_id', $room->id)->count() >= $room->capacity) {
                throw BusinessRuleException::make('Room capacity reached — cannot add more beds.', 'ROOM_CAPACITY');
            }

            if (empty($data['bed_code'])) {
                $data['bed_code'] = $this->numbers->next('hostel.bed', (int) $data['school_id'], Auth::id());
            } else {
                $this->numbers->reserve('hostel.bed', (string) $data['bed_code'], (int) $data['school_id'], Auth::id());
            }

            return Bed::query()->create($data);
        });
    }
}
