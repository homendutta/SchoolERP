<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use App\Platform\Enums\GatewayMode;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $table = 'payment_gateways';

    /** Supported providers. */
    public const PROVIDERS = ['razorpay', 'phonepe', 'payu', 'cashfree'];

    protected $fillable = [
        'provider', 'key_id', 'key_secret', 'config',
        'mode', 'is_enabled', 'is_default',
    ];

    protected $hidden = ['key_id', 'key_secret'];

    protected function casts(): array
    {
        return [
            'key_id' => 'encrypted',
            'key_secret' => 'encrypted',
            'config' => 'array',
            'mode' => GatewayMode::class,
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
