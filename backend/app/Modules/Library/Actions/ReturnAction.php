<?php

declare(strict_types=1);

namespace App\Modules\Library\Actions;

use App\Modules\Library\Models\Borrowing;
use App\Modules\Library\Services\BorrowingEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\Auth;

/** Return a borrowed copy — computes late days + fine (Finance collects). */
class ReturnAction implements Action
{
    use AsAction;

    public function __construct(private readonly BorrowingEngine $engine) {}

    /**
     * @param  array{borrowing_id:int, return_date?:string|null, damage_notes?:string|null}  $payload
     */
    public function handle(array $payload): Borrowing
    {
        $borrowing = Borrowing::query()->findOrFail($payload['borrowing_id']);

        return $this->engine->returnCopy(
            $borrowing,
            $payload['return_date'] ?? null,
            $payload['damage_notes'] ?? null,
            Auth::id(),
        );
    }
}
