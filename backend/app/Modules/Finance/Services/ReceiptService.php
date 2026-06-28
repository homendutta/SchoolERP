<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Payment;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Identity\Models\Identity;

/**
 * Assembles receipt DATA (no PDF rendering). Reuses the Identity Platform for
 * the QR and the live due tracking for the outstanding balance.
 */
class ReceiptService
{
    public function __construct(private readonly DueTrackingService $dues) {}

    /**
     * @return array<string, mixed>
     */
    public function forPayment(int $paymentId): array
    {
        $payment = Payment::query()
            ->with(['allocations.item:id,name', 'method:id,label'])
            ->findOrFail($paymentId);
        $student = Student::query()->findOrFail($payment->student_id);
        $identity = $student->identity_id !== null ? Identity::query()->find($student->identity_id) : null;

        return [
            'receipt_number' => $payment->receipt_number,
            'transaction_number' => $payment->transaction_number,
            'paid_on' => $payment->paid_on?->toDateString(),
            'amount' => $payment->amount,
            'payment_method' => $payment->method?->label,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'admission_number' => $student->admission_number,
                'photo_media_id' => $student->photo_media_id,
            ],
            'identity' => $identity !== null ? [
                'identity_number' => $identity->identity_number,
                'public_identifier' => $identity->public_identifier,
                'qr_url' => "/api/v1/identity/{$identity->id}/qr",
            ] : null,
            'breakdown' => $payment->allocations->map(fn ($a) => [
                'item' => $a->item?->name,
                'amount' => $a->amount,
            ])->values(),
            'outstanding_balance' => $this->dues->forStudent($student->id)['outstanding'],
        ];
    }
}
