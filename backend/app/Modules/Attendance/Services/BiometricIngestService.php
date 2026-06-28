<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\DTO\AttendanceMarkData;
use App\Modules\Attendance\Enums\AttendanceSource;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Enums\BiometricProcessingStatus;
use App\Modules\Attendance\Models\BiometricDevice;
use App\Modules\Attendance\Models\BiometricLog;
use App\Modules\Attendance\Support\Biometric\ConnectorRegistry;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Carbon;

/**
 * Bridges biometric devices and the Attendance Engine. Every raw event becomes
 * an immutable biometric_log first, then — via the Identity Platform — is mapped
 * to attendance. People are matched by Identity Number, never a student/staff id.
 */
class BiometricIngestService extends BaseService
{
    public function __construct(
        private readonly AttendanceEngine $engine,
        private readonly ConnectorRegistry $registry,
    ) {}

    /**
     * Ingest a single, already-normalised event.
     *
     * @param  array<string, mixed>  $rawPayload
     */
    public function ingestEvent(
        int $schoolId,
        ?string $deviceIdentifier,
        string $identityNumber,
        string $eventTime,
        ?string $direction = null,
        array $rawPayload = [],
    ): BiometricLog {
        return $this->transaction(function () use ($schoolId, $deviceIdentifier, $identityNumber, $eventTime, $direction, $rawPayload): BiometricLog {
            $device = $deviceIdentifier !== null
                ? BiometricDevice::query()->where('school_id', $schoolId)->where('device_identifier', $deviceIdentifier)->first()
                : null;
            $device?->forceFill(['last_sync_at' => now()])->save();

            $log = BiometricLog::create([
                'school_id' => $schoolId,
                'device_id' => $device?->id,
                'identity_number' => $identityNumber,
                'event_time' => $eventTime,
                'direction' => $direction,
                'raw_payload' => $rawPayload === [] ? null : $rawPayload,
                'processing_status' => BiometricProcessingStatus::Pending->value,
            ]);

            // Identity-based matching, scoped to the school.
            $identity = Identity::query()
                ->where('school_id', $schoolId)
                ->where('identity_number', $identityNumber)
                ->first();

            if ($identity === null) {
                $log->forceFill(['processing_status' => BiometricProcessingStatus::Unmatched->value])->save();

                return $log;
            }

            $carbon = Carbon::parse($eventTime);
            $date = $carbon->toDateString();
            $time = $carbon->toTimeString();

            $existing = $this->engine->existing($identity->id, $date, null);

            if ($direction === 'out' && $existing !== null) {
                $existing->forceFill(['check_out_time' => $time])->save();
                $attendanceId = $existing->id;
            } elseif ($existing !== null) {
                $attendanceId = $existing->id;
            } else {
                $record = $this->engine->mark($identity, new AttendanceMarkData(
                    date: $date,
                    status: AttendanceStatus::Present,
                    source: AttendanceSource::Biometric,
                    checkInTime: $time,
                    biometricLogId: $log->id,
                    mode: 'skip',
                ));
                $attendanceId = $record->id;
            }

            $log->forceFill([
                'processing_status' => BiometricProcessingStatus::Processed->value,
                'attendance_id' => $attendanceId,
            ])->save();

            return $log;
        });
    }

    /**
     * Ingest a raw vendor payload via its connector (e.g. eSSL MB20). Connector
     * normalises device-specific data; the engine stays vendor-independent.
     *
     * @param  array<string, mixed>  $raw
     * @return array{processed:int, unmatched:int}
     */
    public function ingestRaw(int $schoolId, string $vendor, ?string $deviceIdentifier, array $raw): array
    {
        $connector = $this->registry->get($vendor);
        if ($connector === null) {
            throw BusinessRuleException::make("No biometric connector registered for '{$vendor}'.", 'CONNECTOR_NOT_FOUND');
        }

        $processed = 0;
        $unmatched = 0;

        foreach ($connector->normalize($raw) as $event) {
            $log = $this->ingestEvent($schoolId, $deviceIdentifier, $event['identity_number'], $event['event_time'], $event['direction'] ?? null, $event);
            $log->processing_status === BiometricProcessingStatus::Unmatched ? $unmatched++ : $processed++;
        }

        return ['processed' => $processed, 'unmatched' => $unmatched];
    }
}
