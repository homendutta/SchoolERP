<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Actions\BorrowAction;
use App\Modules\Library\Actions\RenewAction;
use App\Modules\Library\Actions\ReserveAction;
use App\Modules\Library\Actions\ReturnAction;
use App\Modules\Library\Http\Requests\CirculationRequest;
use App\Modules\Library\Http\Resources\BorrowingResource;
use App\Modules\Library\Http\Resources\ReservationResource;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

/**
 * Circulation — the only way copies move. Borrowers are resolved through the
 * Identity Platform; a physical copy is always the borrowable unit.
 */
class CirculationController extends BaseController
{
    public function borrow(CirculationRequest $request, BorrowAction $action): JsonResponse
    {
        /** @var array{school_id:int, identity_number:string, copy_id:int} $data */
        $data = $request->validated();
        $borrowing = $action->handle($data)->load(['book:id,title', 'copy:id,copy_number', 'owner', 'identity:id,identity_number']);

        return $this->ok(new BorrowingResource($borrowing), 'Borrowed.', 201);
    }

    public function returnCopy(CirculationRequest $request, ReturnAction $action): JsonResponse
    {
        /** @var array{borrowing_id:int, return_date?:string|null, damage_notes?:string|null} $data */
        $data = $request->validated();
        $borrowing = $action->handle($data)->load(['book:id,title', 'copy:id,copy_number', 'owner']);

        return $this->ok(new BorrowingResource($borrowing), 'Returned.');
    }

    public function renew(CirculationRequest $request, RenewAction $action): JsonResponse
    {
        /** @var array{borrowing_id:int} $data */
        $data = $request->validated();
        $borrowing = $action->handle($data)->load(['book:id,title', 'copy:id,copy_number']);

        return $this->ok(new BorrowingResource($borrowing), 'Renewed.');
    }

    public function reserve(CirculationRequest $request, ReserveAction $action): JsonResponse
    {
        /** @var array{school_id:int, identity_number:string, book_id:int} $data */
        $data = $request->validated();
        $reservation = $action->handle($data)->load(['book:id,title', 'owner', 'identity:id,identity_number']);

        return $this->ok(new ReservationResource($reservation), 'Reserved.', 201);
    }
}
