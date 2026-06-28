<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Finance\Enums\FeePaymentStatus;
use App\Modules\Finance\Enums\LedgerEntryType;
use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Models\PaymentAllocation;
use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Models\StudentFeeItem;
use App\Modules\Finance\Services\LedgerService;
use App\Modules\Finance\Services\StudentFeeService;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Record a payment (what was PAID). Reserves receipt + transaction numbers from
 * the Number Generator, allocates the amount across the student's outstanding
 * fee items (explicit allocations, or auto FIFO by due date), writes the Ledger
 * entry, and the audit log + timeline. Partial payments are fully supported and
 * each payment is its own transaction — Fee Masters are never touched.
 */
class RecordPaymentAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly StudentFeeService $studentFees,
        private readonly LedgerService $ledger,
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @param  array{school_id:int, student_id:int, amount:float, payment_method_id?:int|null, paid_on?:string|null, reference?:string|null, notes?:string|null, gateway?:string|null, allocations?:array<int, array{student_fee_item_id:int, amount:float}>}  $payload
     */
    public function handle(array $payload): Payment
    {
        return DB::transaction(function () use ($payload): Payment {
            $schoolId = $payload['school_id'];
            $userId = Auth::id();

            $payment = Payment::query()->create([
                'school_id' => $schoolId,
                'student_id' => $payload['student_id'],
                'receipt_number' => $this->numbers->next('finance.receipt', $schoolId, $userId),
                'transaction_number' => $this->numbers->next('finance.transaction', $schoolId, $userId),
                'payment_method_id' => $payload['payment_method_id'] ?? null,
                'amount' => $payload['amount'],
                'paid_on' => $payload['paid_on'] ?? now()->toDateString(),
                'reference' => $payload['reference'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'gateway' => $payload['gateway'] ?? null,
                'recorded_by' => $userId,
            ]);

            $allocations = $payload['allocations'] ?? $this->autoAllocate($payload['student_id'], (float) $payload['amount']);
            $affected = [];

            foreach ($allocations as $alloc) {
                $item = StudentFeeItem::query()->find($alloc['student_fee_item_id']);
                if ($item === null || $alloc['amount'] <= 0) {
                    continue;
                }

                PaymentAllocation::query()->create([
                    'school_id' => $schoolId,
                    'payment_id' => $payment->id,
                    'student_fee_item_id' => $item->id,
                    'amount' => $alloc['amount'],
                ]);

                $item->paid_amount = round($item->paid_amount + $alloc['amount'], 2);
                $item->status = FeePaymentStatus::fromAmounts($item->amount, $item->paid_amount)->value;
                $item->save();

                $affected[$item->student_fee_id] = true;
            }

            foreach (array_keys($affected) as $studentFeeId) {
                $fee = StudentFee::query()->find($studentFeeId);
                if ($fee !== null) {
                    $this->studentFees->recompute($fee);
                }
            }

            $this->ledger->record($payment, LedgerEntryType::Credit, (float) $payload['amount'], "Payment {$payment->receipt_number}", $payload['student_id'], $schoolId);

            $this->timeline->record($payload['student_id'], 'finance.payment_received', "Payment received ({$payment->receipt_number})", null, [
                'payment_id' => $payment->id, 'amount' => $payment->amount,
            ]);
            $this->activity->record('finance.payment_recorded', "Payment {$payment->receipt_number}", $payment, ['amount' => $payment->amount], $schoolId, 'finance');

            return $payment->load('allocations');
        });
    }

    /**
     * Auto-allocate FIFO across the student's outstanding items (earliest due
     * first; undated items last).
     *
     * @return array<int, array{student_fee_item_id:int, amount:float}>
     */
    private function autoAllocate(int $studentId, float $amount): array
    {
        $items = StudentFeeItem::query()
            ->whereHas('studentFee', fn ($q) => $q->where('student_id', $studentId))
            ->whereColumn('paid_amount', '<', 'amount')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $remaining = $amount;
        $allocations = [];
        foreach ($items as $item) {
            if ($remaining <= 0) {
                break;
            }
            $outstanding = round($item->amount - $item->paid_amount, 2);
            $take = min($remaining, $outstanding);
            if ($take > 0) {
                $allocations[] = ['student_fee_item_id' => $item->id, 'amount' => round($take, 2)];
                $remaining = round($remaining - $take, 2);
            }
        }

        return $allocations;
    }
}
