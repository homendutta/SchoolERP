<?php

declare(strict_types=1);

namespace App\Platform\Shared\Traits;

use Illuminate\Support\Str;

/** Auto-assigns a UUID on create for models with a `uuid` column. */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
