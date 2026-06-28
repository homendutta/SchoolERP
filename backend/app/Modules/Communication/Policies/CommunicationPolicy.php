<?php

declare(strict_types=1);

namespace App\Modules\Communication\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Communication\Models\CommunicationMessage;
use App\Platform\Shared\Policies\BasePolicy;

class CommunicationPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('communication.view');
    }

    public function view(User $actor, CommunicationMessage $message): bool
    {
        return $actor->hasPermission('communication.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('communication.manage');
    }

    public function send(User $actor): bool
    {
        return $actor->hasPermission('communication.send');
    }
}
