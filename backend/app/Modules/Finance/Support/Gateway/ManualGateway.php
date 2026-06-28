<?php

declare(strict_types=1);

namespace App\Modules\Finance\Support\Gateway;

use Illuminate\Support\Str;

/**
 * Default gateway — represents an offline / manually-recorded collection. Ships
 * by default so the abstraction is usable without any real provider configured.
 */
class ManualGateway implements PaymentGateway
{
    public function provider(): string
    {
        return 'manual';
    }

    /**
     * @param  array{amount:float, currency?:string, student_id?:int, reference?:string}  $order
     * @return array<string, mixed>
     */
    public function initiate(array $order): array
    {
        return [
            'provider' => $this->provider(),
            'order_id' => 'MAN-'.Str::upper(Str::random(10)),
            'amount' => $order['amount'],
            'currency' => $order['currency'] ?? 'INR',
            'status' => 'created',
            // No redirect/checkout — the cashier records the payment directly.
        ];
    }
}
