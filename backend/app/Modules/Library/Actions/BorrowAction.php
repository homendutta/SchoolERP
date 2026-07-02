<?php

declare(strict_types=1);

namespace App\Modules\Library\Actions;

use App\Modules\Library\Models\Borrowing;
use App\Modules\Library\Models\Copy;
use App\Modules\Library\Services\BorrowerResolver;
use App\Modules\Library\Services\BorrowingEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\Auth;

/** Borrow a copy — borrower resolved through the Identity Platform. */
class BorrowAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly BorrowerResolver $resolver,
        private readonly BorrowingEngine $engine,
    ) {}

    /**
     * @param  array{school_id:int, identity_number:string, copy_id:int}  $payload
     */
    public function handle(array $payload): Borrowing
    {
        $identity = $this->resolver->resolve((int) $payload['school_id'], (string) $payload['identity_number']);
        $copy = Copy::query()->findOrFail($payload['copy_id']);

        return $this->engine->borrow($identity, $copy, Auth::id());
    }
}
