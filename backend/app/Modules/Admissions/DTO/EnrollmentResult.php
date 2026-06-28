<?php

declare(strict_types=1);

namespace App\Modules\Admissions\DTO;

use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;

/**
 * Outcome of a successful enrollment: the created records plus the freshly
 * generated login credentials (returned once so they can be shown/sent).
 */
final class EnrollmentResult
{
    /**
     * @param  array{username:string, password:string}  $studentCredentials
     * @param  array{username:string, password:string}  $parentCredentials
     */
    public function __construct(
        public readonly AdmissionApplication $application,
        public readonly Student $student,
        public readonly Guardian $guardian,
        public readonly array $studentCredentials,
        public readonly array $parentCredentials,
    ) {}
}
