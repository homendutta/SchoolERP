<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use App\Platform\Enums\ResetPolicy;
use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    protected $fillable = [
        'school_id', 'key', 'label', 'initial_number', 'current_number', 'maximum_number',
        'prefix', 'suffix', 'padding', 'increment', 'manual_entry_allowed',
        'format', 'reset_policy', 'last_reset_period', 'last_reset_at',
    ];

    protected function casts(): array
    {
        return [
            'initial_number' => 'integer',
            'current_number' => 'integer',
            'maximum_number' => 'integer',
            'padding' => 'integer',
            'increment' => 'integer',
            'manual_entry_allowed' => 'boolean',
            'reset_policy' => ResetPolicy::class,
            'last_reset_at' => 'datetime',
        ];
    }
}
