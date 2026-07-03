<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Enums\LoanStatus;
use App\Modules\Payroll\Enums\PayrollRunStatus;
use App\Modules\Payroll\Models\Loan;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\PayslipLine;
use Illuminate\Database\Eloquent\Builder;

class PayrollDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $runs = fn (): Builder => PayrollRun::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $payslips = fn (): Builder => Payslip::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        $totalNet = (clone $payslips())->sum('net_pay');
        $totalDeductions = (clone $payslips())->sum('total_deductions');
        $totalEmployer = (clone $payslips())->sum('employer_contributions');
        $totalCost = (float) $totalNet + (float) $totalEmployer;

        return [
            'widgets' => [
                'employees_processed' => (clone $payslips())->distinct('staff_id')->count('staff_id'),
                'pending_payroll' => (clone $runs())->whereIn('status', [PayrollRunStatus::Draft->value, PayrollRunStatus::Processing->value])->count(),
                'payroll_cost' => round($totalCost, 2),
                'net_salary' => round((float) $totalNet, 2),
                'deductions' => round((float) $totalDeductions, 2),
                'employer_contributions' => round((float) $totalEmployer, 2),
                'pending_loans' => Loan::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                    ->where('status', LoanStatus::Pending->value)->count(),
                'payroll_runs' => (clone $runs())->count(),
            ],
            'charts' => [
                'payroll_trend' => (clone $runs())->where('status', '!=', PayrollRunStatus::Cancelled->value)->get(['period_year', 'period_month', 'total_net'])
                    ->groupBy(fn ($r) => sprintf('%04d-%02d', $r->period_year, $r->period_month))
                    ->map(fn ($g, $p) => ['label' => $p, 'count' => (int) round((float) $g->sum('total_net'))])
                    ->sortKeys()->values()->take(-12)->all(),
                'department_cost' => (clone $payslips())->get(['net_pay'])
                    ->groupBy(fn () => 'All')
                    ->map(fn ($g, $k) => ['label' => (string) $k, 'count' => (int) round((float) $g->sum('net_pay'))])
                    ->values()->all(),
                'salary_distribution' => (clone $payslips())->get(['net_pay'])
                    ->groupBy(fn ($p) => $this->band((float) $p->net_pay))
                    ->map(fn ($g, $band) => ['label' => (string) $band, 'count' => $g->count()])
                    ->values()->all(),
                'deduction_breakdown' => PayslipLine::query()
                    ->whereIn('payslip_id', (clone $payslips())->select('id'))
                    ->where('line_type', 'deduction')->get(['name', 'amount'])
                    ->groupBy('name')
                    ->map(fn ($g, $name) => ['label' => (string) $name, 'count' => (int) round((float) $g->sum('amount'))])
                    ->values()->all(),
            ],
        ];
    }

    private function band(float $net): string
    {
        return match (true) {
            $net < 20000 => '< 20k',
            $net < 40000 => '20k–40k',
            $net < 60000 => '40k–60k',
            $net < 100000 => '60k–100k',
            default => '100k+',
        };
    }
}
