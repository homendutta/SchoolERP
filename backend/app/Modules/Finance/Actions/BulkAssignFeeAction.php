<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Models\FeeStructure;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\DB;

/**
 * Bulk-assign a Fee Structure to a whole class/section, or to an explicit list
 * of students. Each assignment reuses the single AssignFeeStructureAction.
 */
class BulkAssignFeeAction implements Action
{
    use AsAction;

    public function __construct(private readonly AssignFeeStructureAction $assign) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{assigned:int}
     */
    public function handle(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $structure = FeeStructure::query()->findOrFail($payload['structure_id']);

            /** @var array<int, int> $studentIds */
            $studentIds = $payload['student_ids'] ?? $this->resolveByClass($payload);

            $assigned = 0;
            foreach (array_unique($studentIds) as $studentId) {
                $student = Student::query()->find($studentId);
                if ($student !== null) {
                    $this->assign->handle($student, $structure);
                    $assigned++;
                }
            }

            return ['assigned' => $assigned];
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, int>
     */
    private function resolveByClass(array $payload): array
    {
        return StudentAcademicRecord::query()
            ->where('is_current', true)
            ->when($payload['class_id'] ?? null, fn ($q, $c) => $q->where('class_id', $c))
            ->when($payload['section_id'] ?? null, fn ($q, $s) => $q->where('section_id', $s))
            ->when($payload['academic_year_id'] ?? null, fn ($q, $y) => $q->where('academic_year_id', $y))
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
