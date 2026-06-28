<?php

declare(strict_types=1);

namespace App\Modules\Attendance\DTO;

use App\Modules\Attendance\Enums\AttendanceSource;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Platform\Shared\DTO\DataTransferObject;

/**
 * Normalised input for the Attendance Engine. The same DTO is produced by manual
 * marking, import and the biometric connector — the engine never knows the
 * source beyond the recorded `source` value.
 */
final class AttendanceMarkData extends DataTransferObject
{
    public function __construct(
        public readonly string $date,
        public readonly AttendanceStatus $status,
        public readonly AttendanceSource $source,
        public readonly ?int $sessionId = null,
        public readonly bool $isLate = false,
        public readonly ?string $remarks = null,
        public readonly ?string $checkInTime = null,
        public readonly ?string $checkOutTime = null,
        public readonly ?string $shift = null,
        public readonly ?int $biometricLogId = null,
        public readonly ?int $recordedBy = null,
        /** create = fail on duplicate · skip = ignore duplicate · correct = update existing */
        public readonly string $mode = 'create',
    ) {}
}
