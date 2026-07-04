<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Models;

use App\Modules\Integrations\Enums\HealthStatus;
use App\Modules\Integrations\Enums\ProviderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A registered integration provider. Its configuration is stored ENCRYPTED. */
class Provider extends Model
{
    use SoftDeletes;

    protected $table = 'integration_providers';

    protected $fillable = [
        'school_id', 'category_id', 'name', 'code', 'adapter', 'version', 'status',
        'config', 'health', 'priority', 'is_default', 'last_checked_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'disabled', 'health' => 'unknown', 'priority' => 100, 'is_default' => false];

    protected function casts(): array
    {
        return [
            'config' => 'encrypted:array',
            'priority' => 'integer',
            'is_default' => 'boolean',
            'last_checked_at' => 'datetime',
            'status' => ProviderStatus::class,
            'health' => HealthStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
