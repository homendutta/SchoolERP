<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\SiblingDiscountRule;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Collection;

/**
 * Resolves a student's sibling order from shared guardians and the configurable
 * sibling rule that applies. Rules are never hardcoded.
 */
class SiblingDiscountService
{
    /**
     * Siblings = students sharing at least one guardian. Ordered by enrolment
     * (oldest first) so child_order is stable; the queried student's 1-based
     * position is their "child order".
     */
    public function childOrder(Student $student): int
    {
        $guardianIds = $student->guardians()->pluck('guardians.id');
        if ($guardianIds->isEmpty()) {
            return 1;
        }

        /** @var Collection<int, Student> $siblings */
        $siblings = Student::query()
            ->where('school_id', $student->school_id)
            ->whereHas('guardians', fn ($q) => $q->whereIn('guardians.id', $guardianIds))
            ->orderBy('enrolled_on')
            ->orderBy('id')
            ->get(['id', 'enrolled_on']);

        $position = $siblings->search(fn (Student $s) => $s->id === $student->id);

        return $position === false ? 1 : $position + 1;
    }

    /** The configurable rule for this student's child order (if any). */
    public function ruleFor(Student $student): ?SiblingDiscountRule
    {
        $order = $this->childOrder($student);
        if ($order < 2) {
            return null;
        }

        return SiblingDiscountRule::query()
            ->where('school_id', $student->school_id)
            ->where('status', 'active')
            ->where('child_order', '<=', $order)
            ->orderByDesc('child_order')
            ->first();
    }
}
