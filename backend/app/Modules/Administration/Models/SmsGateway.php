<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use App\Platform\Enums\GatewayMode;
use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $table = 'sms_gateways';

    protected $fillable = [
        'provider', 'api_url', 'api_key', 'sender_id', 'config',
        'mode', 'is_enabled', 'is_default',
    ];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'config' => 'array',
            'mode' => GatewayMode::class,
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
