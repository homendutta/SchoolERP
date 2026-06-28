<?php

declare(strict_types=1);

namespace App\Modules\Communication\Models;

use App\Modules\Communication\Enums\BackoffStrategy;
use App\Modules\Communication\Enums\CommunicationChannel;
use Illuminate\Database\Eloquent\Model;

/** Per-school channel configuration + retry policy. */
class ChannelSetting extends Model
{
    protected $table = 'communication_channel_settings';

    protected $fillable = [
        'school_id', 'channel', 'is_enabled', 'provider', 'config',
        'max_attempts', 'retry_delay_seconds', 'backoff',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['is_enabled' => true, 'max_attempts' => 3, 'retry_delay_seconds' => 60, 'backoff' => 'exponential'];

    protected function casts(): array
    {
        return [
            'channel' => CommunicationChannel::class,
            'is_enabled' => 'boolean',
            'config' => 'array',
            'max_attempts' => 'integer',
            'retry_delay_seconds' => 'integer',
            'backoff' => BackoffStrategy::class,
        ];
    }
}
