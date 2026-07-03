<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Controllers;

use App\Modules\HumanResources\Http\Resources\SimpleResource;
use App\Modules\HumanResources\Services\LeaveBalanceService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;

/** Read-only leave balances (maintained by the Leave Engine). */
class LeaveBalanceController extends BaseCrudController
{
    public function __construct(private readonly LeaveBalanceService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }
}
