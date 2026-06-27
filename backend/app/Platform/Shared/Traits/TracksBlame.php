<?php

declare(strict_types=1);

namespace App\Platform\Shared\Traits;

use Illuminate\Support\Facades\Auth;

/** Stamps created_by / updated_by from the authenticated user. */
trait TracksBlame
{
    protected static function bootTracksBlame(): void
    {
        static::creating(function ($model): void {
            $id = Auth::id();
            if ($id !== null) {
                $model->created_by ??= $id;
                $model->updated_by ??= $id;
            }
        });

        static::updating(function ($model): void {
            $id = Auth::id();
            if ($id !== null) {
                $model->updated_by = $id;
            }
        });
    }
}
