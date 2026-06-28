<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Policies;

use App\Modules\Administration\Models\User;
use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Policies\BasePolicy;

class MediaPolicy extends BasePolicy
{
    public function view(User $actor, Media $media): bool
    {
        return $actor->hasPermission('media.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('media.upload');
    }

    public function update(User $actor, Media $media): bool
    {
        return $actor->hasPermission('media.upload');
    }

    public function delete(User $actor, Media $media): bool
    {
        return $actor->hasPermission('media.delete');
    }
}
