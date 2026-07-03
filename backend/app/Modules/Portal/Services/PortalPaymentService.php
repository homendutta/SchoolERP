<?php

declare(strict_types=1);

namespace App\Modules\Portal\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Finance\Actions\RecordPaymentAction;
use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Services\ReceiptService;
use App\Modules\Finance\Support\Gateway\GatewayRegistry;
use App\Platform\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Online fee payment for the portals. It owns NO payment logic — it reuses the
 * Finance Payment Engine (RecordPaymentAction) and the vendor-independent Gateway
 * abstraction. Parents may pay for multiple linked children in a single atomic
 * transaction (one Finance payment/receipt per child); students may pay only
 * their own fees. Finance remains the source of truth.
 */
class PortalPaymentService
{
    public function __construct(
        private readonly PortalContextService $context,
        private readonly ReceiptService $receipts,
        private readonly GatewayRegistry $gateways,
        private readonly RecordPaymentAction $recordPayment,
    ) {}

    /** @return array<int, string> */
    public function gatewayProviders(): array
    {
        return $this->gateways->providers();
    }

    /**
     * Pay one or more children's fees. Each item allocates through the Finance
     * engine (explicit allocations or auto FIFO). Multi-child is atomic.
     *
     * @param  array<int, array{student_id:int, amount:float, reference?:string|null, allocations?:array<int, array{student_fee_item_id:int, amount:float}>}>  $items
     * @return array<string, mixed>
     */
    public function pay(User $user, array $items, ?string $gateway = null): array
    {
        $this->context->requireFeePayer($user);

        if ($items === []) {
            throw new DomainException('No fee items were provided to pay.', 422, 'NO_ITEMS');
        }

        // Validate the gateway up front (abstraction only; no real charge).
        $provider = $gateway ?? 'manual';
        if (! $this->gateways->has($provider)) {
            throw new DomainException("Payment gateway '{$provider}' is not configured.", 422, 'GATEWAY_NOT_FOUND');
        }

        $receipts = DB::transaction(fn (): array => array_map(
            fn (array $item): array => $this->charge($user, $item, $provider),
            $items,
        ));

        return [
            'gateway' => $provider,
            'total' => round(array_sum(array_column($receipts, 'amount')), 2),
            'payments' => $receipts,
        ];
    }

    /**
     * Charge a single authorized child through the Finance Payment Engine.
     *
     * @param  array{student_id:int, amount:float, reference?:string|null, allocations?:array<int, array{student_fee_item_id:int, amount:float}>}  $item
     * @return array{student_id:int, payment_id:int, receipt_number:string|null, amount:float}
     */
    private function charge(User $user, array $item, string $provider): array
    {
        $student = $this->context->authorizeStudent($user, (int) $item['student_id']);
        $amount = (float) $item['amount'];
        if ($amount <= 0) {
            throw new DomainException('Payment amount must be positive.', 422, 'INVALID_AMOUNT');
        }

        $payment = ! empty($item['allocations'])
            ? $this->recordPayment->handle([
                'school_id' => (int) $student->school_id,
                'student_id' => (int) $student->id,
                'amount' => $amount,
                'gateway' => $provider,
                'reference' => $item['reference'] ?? null,
                'allocations' => $item['allocations'],
            ])
            : $this->recordPayment->handle([
                'school_id' => (int) $student->school_id,
                'student_id' => (int) $student->id,
                'amount' => $amount,
                'gateway' => $provider,
                'reference' => $item['reference'] ?? null,
            ]);

        return [
            'student_id' => (int) $student->id,
            'payment_id' => (int) $payment->id,
            'receipt_number' => $payment->receipt_number,
            'amount' => (float) $payment->amount,
        ];
    }

    /**
     * Return a receipt, enforcing that the user may access the payment's student.
     *
     * @return array<string, mixed>
     */
    public function receipt(User $user, int $paymentId): array
    {
        $payment = Payment::query()->findOrFail($paymentId);
        $this->context->authorizeStudent($user, (int) $payment->student_id);

        return $this->receipts->forPayment($paymentId);
    }
}
