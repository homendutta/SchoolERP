<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Support\Biometric;

/**
 * Connector for eSSL MB20 (and compatible eSSL) devices. Translates the device's
 * raw punch records into generic attendance events. This is the ONLY place eSSL
 * specifics live; the Attendance Engine never sees them.
 */
class EsslMb20Connector implements BiometricConnector
{
    public function vendor(): string
    {
        return 'essl_mb20';
    }

    /**
     * Accepts a payload shaped like:
     *   { "records": [ { "user_id": "123456", "time": "2026-06-28 09:01:00", "inout": 0 }, ... ] }
     * The user-id reported by an eSSL device is enrolled as the person's
     * Identity Number — never a student/staff database id.
     *
     * @param  array<string, mixed>  $raw
     * @return array<int, array{identity_number:string, event_time:string, direction:?string}>
     */
    public function normalize(array $raw): array
    {
        $records = $raw['records'] ?? $raw['data'] ?? [];
        if (! is_array($records)) {
            return [];
        }

        $events = [];
        foreach ($records as $record) {
            if (! is_array($record)) {
                continue;
            }

            $identityNumber = (string) ($record['user_id'] ?? $record['enrollid'] ?? $record['pin'] ?? '');
            $time = (string) ($record['time'] ?? $record['timestamp'] ?? $record['event_time'] ?? '');
            if ($identityNumber === '' || $time === '') {
                continue;
            }

            $events[] = [
                'identity_number' => $identityNumber,
                'event_time' => $time,
                'direction' => $this->direction($record['inout'] ?? $record['mode'] ?? $record['direction'] ?? null),
            ];
        }

        return $events;
    }

    private function direction(mixed $value): ?string
    {
        return match (true) {
            in_array($value, [0, '0', 'in', 'IN', 'checkin'], true) => 'in',
            in_array($value, [1, '1', 'out', 'OUT', 'checkout'], true) => 'out',
            default => null,
        };
    }
}
