<?php

declare(strict_types=1);

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\BusinessNumber;
use App\Modules\Administration\Models\NumberSequence;
use App\Platform\Enums\ResetPolicy;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Carbon;

/**
 * Centralized Number Generator.
 *
 * Issues official numbers from a configurable sequence (initial/current number,
 * prefix/suffix/format/padding/increment, reset policy, maximum), guarantees
 * uniqueness, records each issue in the Business Number Registry, and resets on
 * the configured period boundary. Increment + registry write are atomic under a
 * row lock.
 */
class NumberGeneratorService extends BaseService
{
    /** Generate and reserve the next number for a key. */
    public function next(string $key, ?int $schoolId = null, ?int $issuedBy = null): string
    {
        return $this->transaction(function () use ($key, $schoolId, $issuedBy) {
            $sequence = $this->lockSequence($key, $schoolId);
            $this->applyReset($sequence);

            $number = $this->nextValue($sequence);
            $this->guardMaximum($sequence, $number);

            $formatted = $this->format($sequence, $number);

            $sequence->current_number = $number;
            $sequence->save();

            BusinessNumber::create([
                'school_id' => $schoolId,
                'type' => $key,
                'number' => $formatted,
                'sequence_id' => $sequence->id,
                'issued_by' => $issuedBy,
                'generated_at' => now(),
            ]);

            return $formatted;
        });
    }

    /** Preview the next number without consuming it. */
    public function peek(string $key, ?int $schoolId = null): string
    {
        $sequence = $this->findSequence($key, $schoolId)
            ?? new NumberSequence(['key' => $key, 'initial_number' => 1, 'current_number' => 0, 'increment' => 1]);

        return $this->format($sequence, $this->nextValue($sequence));
    }

    /** Reset the sequence back to its initial number (permission-protected at the controller). */
    public function reset(string $key, ?int $schoolId = null): NumberSequence
    {
        return $this->transaction(function () use ($key, $schoolId) {
            $sequence = $this->lockSequence($key, $schoolId);
            $policy = $sequence->reset_policy instanceof ResetPolicy ? $sequence->reset_policy : ResetPolicy::None;

            $sequence->current_number = 0;
            $sequence->last_reset_at = now();
            $sequence->last_reset_period = $policy->currentPeriod(Carbon::now());
            $sequence->save();

            return $sequence;
        });
    }

    /** History of issued numbers for a key (most recent first). */
    public function history(string $key, ?int $schoolId = null, int $limit = 50): array
    {
        return BusinessNumber::query()
            ->where('type', $key)
            ->where('school_id', $schoolId)
            ->latest('generated_at')
            ->limit($limit)
            ->get(['number', 'issued_by', 'generated_at'])
            ->toArray();
    }

    private function lockSequence(string $key, ?int $schoolId): NumberSequence
    {
        $sequence = NumberSequence::query()
            ->where('school_id', $schoolId)->where('key', $key)
            ->lockForUpdate()->first();

        if ($sequence === null) {
            $created = NumberSequence::create(['school_id' => $schoolId, 'key' => $key]);
            $sequence = NumberSequence::query()->whereKey($created->id)->lockForUpdate()->first();
        }

        return $sequence;
    }

    private function findSequence(string $key, ?int $schoolId): ?NumberSequence
    {
        return NumberSequence::query()->where('school_id', $schoolId)->where('key', $key)->first();
    }

    private function nextValue(NumberSequence $sequence): int
    {
        $increment = max(1, (int) $sequence->increment);

        return (int) $sequence->current_number === 0
            ? (int) $sequence->initial_number
            : (int) $sequence->current_number + $increment;
    }

    private function applyReset(NumberSequence $sequence): void
    {
        $policy = $sequence->reset_policy instanceof ResetPolicy ? $sequence->reset_policy : ResetPolicy::None;
        $period = $policy->currentPeriod(Carbon::now());

        if ($period !== null && $sequence->last_reset_period !== $period) {
            $sequence->current_number = 0;
            $sequence->last_reset_period = $period;
            $sequence->last_reset_at = now();
        }
    }

    private function guardMaximum(NumberSequence $sequence, int $number): void
    {
        if ($sequence->maximum_number !== null && $number > (int) $sequence->maximum_number) {
            throw BusinessRuleException::make(
                "Number sequence '{$sequence->key}' has reached its maximum.",
                'SEQUENCE_EXHAUSTED',
            );
        }
    }

    private function format(NumberSequence $sequence, int $number): string
    {
        $padded = $sequence->padding > 0
            ? str_pad((string) $number, (int) $sequence->padding, '0', STR_PAD_LEFT)
            : (string) $number;

        return strtr($sequence->format ?? '{prefix}{number}{suffix}', [
            '{prefix}' => $sequence->prefix ?? '',
            '{suffix}' => $sequence->suffix ?? '',
            '{year}' => Carbon::now()->format('Y'),
            '{number}' => $padded,
        ]);
    }
}
