<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Finance\Enums\AdjustmentType;
use App\Modules\Finance\Enums\LedgerEntryType;
use App\Modules\Finance\Models\Adjustment;
use App\Modules\Finance\Services\LedgerService;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Create a financial adjustment (credit/debit note, waiver, manual). Always an
 * INDEPENDENT record — never modifies historical payments or fee masters. Writes
 * a ledger entry; due tracking factors active adjustments into outstanding.
 */
class AdjustmentAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly LedgerService $ledger,
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @param  array{school_id:int, student_id:int, type:string, amount:float, reason?:string|null, student_fee_id?:int|null}  $payload
     */
    public function handle(array $payload): Adjustment
    {
        return DB::transaction(function () use ($payload): Adjustment {
            $type = AdjustmentType::from($payload['type']);

            $adjustment = Adjustment::query()->create([
                'school_id' => $payload['school_id'],
                'student_id' => $payload['student_id'],
                'student_fee_id' => $payload['student_fee_id'] ?? null,
                'transaction_number' => $this->numbers->next('finance.transaction', $payload['school_id'], Auth::id()),
                'type' => $type->value,
                'amount' => $payload['amount'],
                'reason' => $payload['reason'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Credit notes / waivers reduce due (credit the student); debit notes increase it.
            $ledgerType = $type->reducesDue() ? LedgerEntryType::Credit : LedgerEntryType::Debit;
            $this->ledger->record($adjustment, $ledgerType, (float) $payload['amount'], "Adjustment {$adjustment->transaction_number} ({$type->label()})", $payload['student_id'], $payload['school_id']);

            $this->timeline->record($payload['student_id'], 'finance.adjustment_created', "{$type->label()} ({$adjustment->transaction_number})", $payload['reason'] ?? null, ['adjustment_id' => $adjustment->id, 'amount' => $payload['amount']]);
            $this->activity->record('finance.adjustment_created', "Adjustment {$adjustment->transaction_number}", $adjustment, ['type' => $type->value, 'amount' => $payload['amount']], $payload['school_id'], 'finance');

            return $adjustment;
        });
    }
}
