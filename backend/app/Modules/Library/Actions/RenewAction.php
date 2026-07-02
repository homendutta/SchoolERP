<?php

declare(strict_types=1);

namespace App\Modules\Library\Actions;

use App\Modules\Library\Models\Borrowing;
use App\Modules\Library\Services\BorrowingEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\Auth;

/** Renew a borrowing (extend the due date), respecting limits & reservations. */
class RenewAction implements Action
{
    use AsAction;

    public function __construct(private readonly BorrowingEngine $engine) {}

    /**
     * @param  array{borrowing_id:int}  $payload
     */
    public function handle(array $payload): Borrowing
    {
        $borrowing = Borrowing::query()->findOrFail($payload['borrowing_id']);

        return $this->engine->renew($borrowing, Auth::id());
    }
}
