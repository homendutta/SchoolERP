<?php

declare(strict_types=1);

namespace App\Platform\Shared\Traits;

use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Schema;

/**
 * Gives a model a permanent platform Identity, created automatically the moment
 * the model is created. The model only declares its IdentityType; the Identity
 * platform owns everything else. Modules never manipulate Identity records.
 */
trait HasIdentity
{
    /** @var array<string, bool> */
    private static array $identityColumnCache = [];

    public static function bootHasIdentity(): void
    {
        static::created(function (self $model): void {
            $identity = app(IdentityService::class)->ensureFor($model, $model->identityType());

            if (self::tableHasIdentityColumn($model->getTable())
                && (int) $model->getAttribute('identity_id') !== $identity->id) {
                $model->forceFill(['identity_id' => $identity->id])->saveQuietly();
            }
        });
    }

    abstract public function identityType(): IdentityType;

    public function identity(): MorphOne
    {
        return $this->morphOne(Identity::class, 'owner');
    }

    private static function tableHasIdentityColumn(string $table): bool
    {
        return self::$identityColumnCache[$table] ??= Schema::hasColumn($table, 'identity_id');
    }
}
