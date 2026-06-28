<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Enums\FeePaymentStatus;
use App\Modules\Finance\Models\StudentDiscount;
use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Models\StudentScholarship;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read/list student fees + recompute the header from items/discounts. */
class StudentFeeService extends BaseCrudService
{
    protected function model(): string
    {
        return StudentFee::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'student:id,name,admission_number,identity_id',
            'items:id,student_fee_id,name,amount,paid_amount,due_date,status,fee_category_id',
            'schoolClass:id,name',
            'section:id,name',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_id', 'academic_year_id', 'class_id', 'section_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at', 'net_amount'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => FeePaymentStatus::class],
            'student' => ['type' => 'relation', 'relation' => 'student', 'columns' => ['name', 'admission_number']],
        ];
    }

    /** Recompute totals/net/paid/status from line items + applied concessions. */
    public function recompute(StudentFee $fee): StudentFee
    {
        $fee->loadMissing('items');
        $total = (float) $fee->items->sum('amount');
        $paid = (float) $fee->items->sum('paid_amount');

        $discount = (float) StudentDiscount::query()
            ->where('student_fee_id', $fee->id)->where('status', 'active')->sum('amount');
        $scholarship = (float) StudentScholarship::query()
            ->where('student_fee_id', $fee->id)->where('status', 'active')->sum('amount');

        $net = max(0.0, round($total - $discount - $scholarship, 2));

        $fee->forceFill([
            'total_amount' => round($total, 2),
            'discount_amount' => round($discount, 2),
            'scholarship_amount' => round($scholarship, 2),
            'net_amount' => $net,
            'paid_amount' => round($paid, 2),
            'status' => FeePaymentStatus::fromAmounts($net, $paid)->value,
        ])->save();

        return $fee->refresh();
    }
}
