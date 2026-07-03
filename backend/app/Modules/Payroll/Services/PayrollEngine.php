<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Payroll\Enums\ComponentType;
use App\Modules\Payroll\Enums\LoanStatus;
use App\Modules\Payroll\Enums\PayrollRunStatus;
use App\Modules\Payroll\Models\Arrear;
use App\Modules\Payroll\Models\Loan;
use App\Modules\Payroll\Models\Overtime;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\PayslipLine;
use App\Modules\Payroll\Models\SalaryAssignment;
use App\Modules\Payroll\Models\StatutoryComponent;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;

/**
 * The reusable Payroll Engine. It CONSUMES HR (salary structures), Attendance and
 * Leave (read-only) and Finance (settlement is recorded elsewhere) — it never
 * edits them. Processing is IDEMPOTENT: a payslip is keyed by (employee, period),
 * so running a run twice never creates a duplicate payroll record. A locked run
 * is immutable; corrections require a new run.
 */
class PayrollEngine extends BaseService
{
    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly AttendanceReader $attendance,
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $timeline,
        private readonly PayrollHooks $hooks,
    ) {}

    /** Process every eligible employee for a run (idempotent), then complete it. */
    public function processRun(PayrollRun $run): PayrollRun
    {
        if ($run->isLocked()) {
            throw BusinessRuleException::make('A locked payroll run cannot be reprocessed.', 'PAYROLL_LOCKED');
        }

        return $this->transaction(function () use ($run): PayrollRun {
            $run->update(['status' => PayrollRunStatus::Processing->value]);

            $assignments = SalaryAssignment::query()
                ->where('school_id', $run->school_id)
                ->where('is_current', true)
                ->with('structure.components.component')
                ->get();

            $earnings = 0.0;
            $deductions = 0.0;
            $employer = 0.0;
            $net = 0.0;
            $count = 0;

            foreach ($assignments as $assignment) {
                $payslip = $this->processEmployee($run, $assignment);
                $earnings += (float) $payslip->gross_earnings;
                $deductions += (float) $payslip->total_deductions;
                $employer += (float) $payslip->employer_contributions;
                $net += (float) $payslip->net_pay;
                $count++;
            }

            $run->update([
                'status' => PayrollRunStatus::Completed->value,
                'total_earnings' => $earnings,
                'total_deductions' => $deductions,
                'total_employer' => $employer,
                'total_net' => $net,
                'processed_count' => $count,
            ]);

            $this->activity->record('payroll.generated', "Payroll generated for {$count} employee(s)", $run, [
                'period' => "{$run->period_year}-{$run->period_month}", 'net' => $net,
            ], (int) $run->school_id, 'payroll');
            $this->hooks->payrollGenerated((int) $run->school_id, "Payroll {$run->run_number} generated for {$count} employee(s).");

            return $run->refresh();
        });
    }

    /**
     * Process a single employee for the run. IDEMPOTENT: if a payslip already
     * exists for this employee + period it is returned unchanged.
     */
    public function processEmployee(PayrollRun $run, SalaryAssignment $assignment): Payslip
    {
        $existing = Payslip::query()
            ->where('staff_id', $assignment->staff_id)
            ->where('period_year', $run->period_year)
            ->where('period_month', $run->period_month)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $staffId = (int) $assignment->staff_id;
        $schoolId = (int) $run->school_id;

        /** @var array<int, array{name:string, code:?string, line_type:string, component_id:?int, amount:float}> $lines */
        $lines = [];
        $amounts = [];            // component code => amount (for percentage base + statutory base)
        $regularEarnings = 0.0;
        $structureDeductions = 0.0;
        $structureEmployer = 0.0;

        // --- Structure components (two passes: fixed first, then percentage) ---
        $components = $assignment->structure?->components ?? collect();
        foreach ([false, true] as $percentagePass) {
            foreach ($components as $sc) {
                $comp = $sc->component;
                if ($comp === null) {
                    continue;
                }
                $isPercentage = $comp->calculation_type->value === 'percentage';
                if ($isPercentage !== $percentagePass) {
                    continue;
                }

                $value = $sc->value !== null ? (float) $sc->value : (float) $comp->default_value;
                $amount = $this->componentAmount($comp->calculation_type->value, $value, $comp->based_on, $amounts);

                if ($comp->code !== null) {
                    $amounts[strtolower($comp->code)] = $amount;
                }

                $type = $comp->component_type;
                if ($type === ComponentType::Informational) {
                    $lines[] = $this->line($comp->name, $comp->code, ComponentType::Informational->value, $amount, $comp->id);

                    continue;
                }

                $lines[] = $this->line($comp->name, $comp->code, $type->value, $amount, $comp->id);
                if ($type === ComponentType::Earning) {
                    $regularEarnings += $amount;
                } elseif ($type === ComponentType::Deduction) {
                    $structureDeductions += $amount;
                } elseif ($type === ComponentType::EmployerContribution) {
                    $structureEmployer += $amount;
                }
            }
        }

        $basicAmount = $amounts['basic'] ?? $regularEarnings;

        // --- Attendance (read-only) → loss of pay ---
        $summary = $this->attendance->summarise($staffId, (int) $run->period_year, (int) $run->period_month);
        $lopDays = $summary['absent'] + 0.5 * $summary['half_day'];
        $lopAmount = 0.0;
        if ($summary['working'] > 0 && $lopDays > 0 && $regularEarnings > 0) {
            $lopAmount = round(($regularEarnings / $summary['working']) * $lopDays, 2);
            $lines[] = $this->line('Loss of Pay', 'LOP', ComponentType::Deduction->value, $lopAmount, null);
        }

        // --- Overtime (approved only) ---
        $overtimeAmount = 0.0;
        $overtimeHours = 0.0;
        foreach (Overtime::query()
            ->where('staff_id', $staffId)
            ->where('period_year', $run->period_year)
            ->where('period_month', $run->period_month)
            ->where('approved', true)->get() as $ot) {
            $overtimeHours += $ot->payableHours();
            $overtimeAmount += round($ot->payableHours() * (float) $ot->hourly_rate, 2);
        }
        if ($overtimeAmount > 0) {
            $lines[] = $this->line('Overtime', 'OT', ComponentType::Earning->value, $overtimeAmount, null);
        }

        // --- Arrears (unapplied) ---
        $arrearAmount = 0.0;
        $arrears = Arrear::query()->where('staff_id', $staffId)->where('applied', false)->get();
        foreach ($arrears as $arrear) {
            $arrearAmount += (float) $arrear->amount;
        }
        if ($arrearAmount > 0) {
            $lines[] = $this->line('Arrears', 'ARR', ComponentType::Earning->value, $arrearAmount, null);
            Arrear::query()->whereIn('id', $arrears->pluck('id'))->update(['applied' => true, 'applied_run_id' => $run->id]);
        }

        // --- Statutory deductions (config only; never hardcoded) ---
        $statutoryEmployee = 0.0;
        $statutoryEmployer = 0.0;
        foreach (StatutoryComponent::query()->where('school_id', $schoolId)->where('status', 'active')->get() as $stat) {
            $base = $stat->based_on === 'gross' ? $regularEarnings : $basicAmount;
            if ($stat->wage_ceiling !== null) {
                $base = min($base, (float) $stat->wage_ceiling);
            }
            $isPercentage = $stat->calculation_type->value === 'percentage';
            $empAmt = round($isPercentage ? $base * (float) $stat->employee_rate / 100 : (float) $stat->employee_rate, 2);
            $erAmt = round($isPercentage ? $base * (float) $stat->employer_rate / 100 : (float) $stat->employer_rate, 2);

            if ($empAmt > 0) {
                $lines[] = $this->line($stat->name, $stat->code, ComponentType::Deduction->value, $empAmt, null);
                $statutoryEmployee += $empAmt;
            }
            if ($erAmt > 0) {
                $lines[] = $this->line($stat->name.' (Employer)', $stat->code, ComponentType::EmployerContribution->value, $erAmt, null);
                $statutoryEmployer += $erAmt;
            }
        }

        // --- Loan / advance installments ---
        $loanRepay = 0.0;
        foreach (Loan::query()->where('staff_id', $staffId)->where('status', LoanStatus::Active->value)->get() as $loan) {
            $installment = min((float) $loan->installment_amount, (float) $loan->balance);
            if ($installment <= 0) {
                continue;
            }
            $lines[] = $this->line('Loan Repayment'.($loan->reference ? " ({$loan->reference})" : ''), 'LOAN', ComponentType::Deduction->value, $installment, null);
            $loanRepay += $installment;

            $newBalance = round((float) $loan->balance - $installment, 2);
            $loan->update([
                'balance' => $newBalance,
                'status' => $newBalance <= 0 ? LoanStatus::Closed->value : LoanStatus::Active->value,
            ]);
        }

        // --- Totals ---
        $grossEarnings = round($regularEarnings + $overtimeAmount + $arrearAmount, 2);
        $totalDeductions = round($structureDeductions + $lopAmount + $statutoryEmployee + $loanRepay, 2);
        $employerContributions = round($structureEmployer + $statutoryEmployer, 2);
        $netPay = round($grossEarnings - $totalDeductions, 2);

        $payslip = Payslip::query()->create([
            'school_id' => $schoolId,
            'run_id' => $run->id,
            'staff_id' => $staffId,
            'assignment_id' => $assignment->id,
            'payslip_number' => $this->numbers->next('payroll_payslip', $schoolId),
            'period_year' => $run->period_year,
            'period_month' => $run->period_month,
            'gross_earnings' => $grossEarnings,
            'total_deductions' => $totalDeductions,
            'employer_contributions' => $employerContributions,
            'net_pay' => $netPay,
            'present_days' => $summary['present'],
            'absent_days' => $summary['absent'],
            'leave_days' => $summary['leave'],
            'lwp_days' => $lopDays,
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
        ]);

        foreach ($lines as $line) {
            PayslipLine::query()->create([
                'payslip_id' => $payslip->id,
                'component_id' => $line['component_id'],
                'name' => $line['name'],
                'code' => $line['code'],
                'line_type' => $line['line_type'],
                'amount' => $line['amount'],
            ]);
        }

        $this->timeline->record($staffId, 'payroll.payslip_generated', 'Payslip generated', $payslip->payslip_number, [
            'payslip_id' => $payslip->id, 'net_pay' => $netPay,
        ]);
        $this->hooks->payslipAvailable($schoolId, "Payslip {$payslip->payslip_number} is available.");

        return $payslip->refresh();
    }

    /** Lock a run — it becomes immutable; corrections require a new run. */
    public function lockRun(PayrollRun $run): PayrollRun
    {
        if ($run->isLocked()) {
            return $run;
        }
        if ($run->status !== PayrollRunStatus::Completed) {
            throw BusinessRuleException::make('Only a completed payroll run can be locked.', 'PAYROLL_NOT_COMPLETED');
        }

        $run->update([
            'status' => PayrollRunStatus::Locked->value,
            'locked_at' => now(),
            'locked_by' => Auth::id(),
        ]);

        $this->activity->record('payroll.locked', 'Payroll run locked', $run, [], (int) $run->school_id, 'payroll');
        $this->hooks->payrollLocked((int) $run->school_id, "Payroll {$run->run_number} locked.");

        return $run->refresh();
    }

    /**
     * @return array{name:string, code:?string, line_type:string, component_id:?int, amount:float}
     */
    private function line(string $name, ?string $code, string $type, float $amount, ?int $componentId): array
    {
        return ['name' => $name, 'code' => $code, 'line_type' => $type, 'amount' => round($amount, 2), 'component_id' => $componentId];
    }

    /**
     * @param  array<string, float>  $amounts
     */
    private function componentAmount(string $calc, float $value, ?string $basedOn, array $amounts): float
    {
        if ($calc === 'percentage') {
            $base = $basedOn !== null ? ($amounts[strtolower($basedOn)] ?? 0.0) : 0.0;

            return round($base * $value / 100, 2);
        }

        // fixed (and formula — future-ready, not evaluated here)
        return $calc === 'formula' ? 0.0 : $value;
    }
}
