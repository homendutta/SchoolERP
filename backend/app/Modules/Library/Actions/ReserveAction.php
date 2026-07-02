<?php

declare(strict_types=1);

namespace App\Modules\Library\Actions;

use App\Modules\Library\Models\Reservation;
use App\Modules\Library\Services\BorrowerResolver;
use App\Modules\Library\Services\BorrowingEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/** Reserve a title — borrower resolved through the Identity Platform. */
class ReserveAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly BorrowerResolver $resolver,
        private readonly BorrowingEngine $engine,
    ) {}

    /**
     * @param  array{school_id:int, identity_number:string, book_id:int}  $payload
     */
    public function handle(array $payload): Reservation
    {
        $identity = $this->resolver->resolve((int) $payload['school_id'], (string) $payload['identity_number']);

        return $this->engine->reserve($identity, (int) $payload['book_id'], (int) $payload['school_id']);
    }
}
