<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\Models\Room;
use App\Modules\Academic\Services\RoomService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class CreateRoomAction implements Action
{
    use AsAction;

    public function __construct(private readonly RoomService $service) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): Room
    {
        /** @var Room $room */
        $room = $this->service->create($data);

        return $room;
    }
}
