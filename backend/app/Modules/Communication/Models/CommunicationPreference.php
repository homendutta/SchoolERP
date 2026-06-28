<?php

declare(strict_types=1);

namespace App\Modules\Communication\Models;

use App\Modules\Communication\Enums\CommunicationChannel;
use Illuminate\Database\Eloquent\Model;

/** A user's per-channel opt-in/out. */
class CommunicationPreference extends Model
{
    protected $table = 'communication_preferences';

    protected $fillable = ['user_id', 'channel', 'is_enabled'];

    /** @var array<string, mixed> */
    protected $attributes = ['is_enabled' => true];

    protected function casts(): array
    {
        return ['channel' => CommunicationChannel::class, 'is_enabled' => 'boolean'];
    }
}
