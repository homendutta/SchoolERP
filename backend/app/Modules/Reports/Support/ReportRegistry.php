<?php

declare(strict_types=1);

namespace App\Modules\Reports\Support;

use App\Modules\Finance\Models\Payment;
use App\Modules\Library\Models\Borrowing;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Audit\Models\ActivityLog;
use App\Platform\Shared\Exceptions\DomainException;

/**
 * The catalog of reusable report definitions. Each definition reads from the
 * OWNING module (never duplicating its logic); the Reporting Engine does the rest.
 * New reports register here — the engine, export and print layers never change.
 */
class ReportRegistry
{
    /** @var array<string, ReportDefinition> */
    private array $definitions = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    public function register(ReportDefinition $definition): void
    {
        $this->definitions[$definition->key] = $definition;
    }

    /** @return array<int, ReportDefinition> */
    public function all(): array
    {
        return array_values($this->definitions);
    }

    public function get(string $key): ReportDefinition
    {
        return $this->definitions[$key]
            ?? throw new DomainException("Unknown report '{$key}'.", 404, 'REPORT_NOT_FOUND');
    }

    public function has(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    private function registerDefaults(): void
    {
        $this->register(new ReportDefinition(
            'academic.student_list', 'academic', 'Academic', 'Student List',
            ['admission_number' => 'Admission No', 'name' => 'Name', 'status' => 'Status'],
            fn (array $p) => Student::query()->where('school_id', $p['school_id'])
                ->orderBy('name')->get(['id', 'admission_number', 'name', 'status'])
                ->map(fn ($s) => ['admission_number' => $s->admission_number, 'name' => $s->name, 'status' => (string) $s->status?->value])->all(),
        ));

        $this->register(new ReportDefinition(
            'hr.employee_list', 'hr', 'HR', 'Employee List',
            ['employee_number' => 'Employee No', 'name' => 'Name', 'status' => 'Status'],
            fn (array $p) => Staff::query()->where('school_id', $p['school_id'])
                ->orderBy('name')->get(['id', 'employee_number', 'name', 'status'])
                ->map(fn ($s) => ['employee_number' => $s->employee_number, 'name' => $s->name, 'status' => (string) $s->status])->all(),
        ));

        $this->register(new ReportDefinition(
            'finance.fee_collection', 'finance', 'Finance', 'Fee Collection',
            ['receipt_number' => 'Receipt', 'student_id' => 'Student', 'amount' => 'Amount', 'paid_on' => 'Paid On'],
            fn (array $p) => Payment::query()->where('school_id', $p['school_id'])
                ->orderByDesc('paid_on')->get(['receipt_number', 'student_id', 'amount', 'paid_on'])
                ->map(fn ($x) => ['receipt_number' => $x->receipt_number, 'student_id' => $x->student_id, 'amount' => (float) $x->amount, 'paid_on' => $x->paid_on?->toDateString()])->all(),
            totals: ['amount'],
        ));

        $this->register(new ReportDefinition(
            'operational.library_borrowings', 'library', 'Operational', 'Library Borrowings',
            ['book_id' => 'Book', 'owner_id' => 'Borrower', 'borrow_date' => 'Borrowed', 'due_date' => 'Due', 'status' => 'Status'],
            fn (array $p) => Borrowing::query()->where('school_id', $p['school_id'])
                ->orderByDesc('borrow_date')->get(['book_id', 'owner_id', 'borrow_date', 'due_date', 'status'])
                ->map(fn ($b) => ['book_id' => $b->book_id, 'owner_id' => $b->owner_id, 'borrow_date' => $b->borrow_date?->toDateString(), 'due_date' => $b->due_date?->toDateString(), 'status' => (string) $b->status?->value])->all(),
        ));

        $this->register(new ReportDefinition(
            'audit.activity', 'audit', 'Audit', 'User Activity',
            ['action' => 'Action', 'log_name' => 'Module', 'description' => 'Description', 'created_at' => 'When'],
            fn (array $p) => ActivityLog::query()->where('school_id', $p['school_id'])
                ->orderByDesc('id')->limit(1000)->get(['action', 'log_name', 'description', 'created_at'])
                ->map(fn ($a) => ['action' => $a->action, 'log_name' => $a->log_name, 'description' => $a->description, 'created_at' => optional($a->created_at)->toDateTimeString()])->all(),
            permission: 'reports.audit',
        ));
    }
}
