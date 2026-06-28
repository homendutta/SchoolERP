<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Finance\Enums\LedgerEntryType;
use App\Modules\Finance\Enums\PaymentStatus;
use App\Modules\Finance\Enums\RefundType;
use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Models\Refund;
use App\Modules\Finance\Services\LedgerService;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Issue a refund. NEVER deletes the payment — it creates an independent refund
 * record, increments the payment's refunded total, and writes a debit ledger
 * entry. Full and partial refunds are supported.
 */
class RefundAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly LedgerService $ledger,
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @param  array{amount:float, type?:string, reason?:string|null}  $payload
     */
    public function handle(Payment $payment, array $payload): Refund
    {
        $amount = (float) $payload['amount'];
        $refundable = round($payment->amount - $payment->refunded_amount, 2);
        if ($amount <= 0 || $amount > $refundable) {
            throw BusinessRuleException::make("Refund amount exceeds the refundable balance ({$refundable}).", 'REFUND_EXCEEDS_BALANCE');
        }

        return DB::transaction(function () use ($payment, $payload, $amount): Refund {
            $type = ($payload['type'] ?? null) === 'full' || $amount >= $payment->amount ? RefundType::Full : RefundType::Partial;

            $refund = Refund::query()->create([
                'school_id' => $payment->school_id,
                'student_id' => $payment->student_id,
                'payment_id' => $payment->id,
                'transaction_number' => $this->numbers->next('finance.transaction', $payment->school_id, Auth::id()),
                'amount' => $amount,
                'type' => $type->value,
                'reason' => $payload['reason'] ?? null,
                'refunded_on' => now()->toDateString(),
                'processed_by' => Auth::id(),
            ]);

            $payment->refunded_amount = round($payment->refunded_amount + $amount, 2);
            if ($payment->refunded_amount >= $payment->amount) {
                $payment->status = PaymentStatus::Refunded->value;
            }
            $payment->save();

            $this->ledger->record($refund, LedgerEntryType::Debit, $amount, "Refund {$refund->transaction_number}", $payment->student_id, $payment->school_id);

            $this->timeline->record($payment->student_id, 'finance.refund_issued', "Refund issued ({$refund->transaction_number})", $payload['reason'] ?? null, ['refund_id' => $refund->id, 'amount' => $amount]);
            $this->activity->record('finance.refund_issued', "Refund {$refund->transaction_number}", $refund, ['amount' => $amount], $payment->school_id, 'finance');

            return $refund;
        });
    }
}
