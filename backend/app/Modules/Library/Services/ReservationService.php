<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Enums\ReservationStatus;
use App\Modules\Library\Models\Reservation;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + queue management over reservations. */
class ReservationService extends BaseCrudService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    protected function model(): string
    {
        return Reservation::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['book:id,title', 'owner', 'identity:id,identity_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'book_id', 'identity_id', 'owner_type', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'queue_position', 'reserved_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['status' => ['type' => 'enum', 'enum' => ReservationStatus::class]];
    }

    public function cancel(Reservation $reservation): Reservation
    {
        $reservation->update(['status' => ReservationStatus::Cancelled->value]);
        $this->activity->record('library.reservation_cancelled', 'Reservation cancelled', $reservation, [], $reservation->school_id, 'library');

        return $reservation->refresh();
    }
}
