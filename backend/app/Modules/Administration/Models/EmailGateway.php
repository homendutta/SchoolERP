<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use App\Platform\Enums\GatewayMode;
use Illuminate\Database\Eloquent\Model;

class EmailGateway extends Model
{
    protected $table = 'email_gateways';

    protected $fillable = [
        'provider', 'host', 'port', 'encryption', 'username', 'password',
        'from_address', 'from_name', 'mode', 'is_enabled', 'is_default',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
            'mode' => GatewayMode::class,
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
