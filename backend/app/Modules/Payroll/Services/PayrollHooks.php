<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;

/**
 * Payroll → Communication integration. Payroll NEVER sends notifications itself;
 * each hook publishes a communication request through the engine.
 */
class PayrollHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function payrollGenerated(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'payroll.generated', 'Payroll generated', $detail);
    }

    public function payslipAvailable(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'payroll.payslip_available', 'Payslip available', $detail);
    }

    public function payrollLocked(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'payroll.locked', 'Payroll locked', $detail);
    }

    public function salaryRevision(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'payroll.salary_revision', 'Salary revision', $detail);
    }

    public function loanApproved(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'payroll.loan_approved', 'Loan approved', $detail);
    }

    private function notify(int $schoolId, string $event, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Administrators,
            subject: $subject,
            body: $body,
            source: 'payroll',
            event: $event,
        ));
    }
}
