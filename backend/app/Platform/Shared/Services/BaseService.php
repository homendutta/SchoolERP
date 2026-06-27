<?php

declare(strict_types=1);

namespace App\Platform\Shared\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Base service for every module.
 *
 * The Service layer is the ONLY place business rules live. This base provides a
 * transaction helper so multi-step workflows (e.g., enrollment, promotion,
 * multi-month payment) commit or roll back atomically, per the backend
 * architecture. No business logic is implemented here.
 */
abstract class BaseService implements ServiceInterface
{
    /**
     * Run a unit of work inside a database transaction.
     *
     * @template T
     *
     * @param  callable(): T  $work
     * @return T
     *
     * @throws Throwable
     */
    protected function transaction(callable $work): mixed
    {
        return DB::transaction($work);
    }
}
