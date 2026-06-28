<?php

declare(strict_types=1);

namespace App\Modules\Finance\Support\Gateway;

/**
 * Vendor-independent online payment gateway contract. Concrete providers
 * (Razorpay, PhonePe, Cashfree, Stripe…) implement this WITHOUT the rest of the
 * module knowing about them. Only the abstraction ships now — no real
 * integration.
 */
interface PaymentGateway
{
    /** Unique provider key (e.g. 'manual', 'razorpay'). */
    public function provider(): string;

    /**
     * Create a payment intent/order. Returns a provider-agnostic descriptor the
     * client uses to complete payment.
     *
     * @param  array{amount:float, currency?:string, student_id?:int, reference?:string}  $order
     * @return array<string, mixed>
     */
    public function initiate(array $order): array;
}
