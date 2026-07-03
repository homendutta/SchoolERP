<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Jobs\GenerateDocumentJob;
use App\Modules\Documents\Models\Template;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\DomainException;

/**
 * Bulk document generation. Resolves a target scope (class / section / examination
 * / academic year / staff department) to its subjects, then QUEUES one
 * GenerateDocumentJob per subject so large runs never block the request.
 */
class BulkGenerationService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * @param  array<string, mixed>  $target
     * @return array{queued:int}
     */
    public function generate(Template $template, string $scope, array $target, string $subjectKind): array
    {
        $ids = $this->resolveSubjectIds($scope, $target, $subjectKind, (int) $template->school_id);
        if ($ids === []) {
            throw new DomainException('No subjects matched the selected scope.', 422, 'NO_SUBJECTS');
        }

        foreach ($ids as $id) {
            GenerateDocumentJob::dispatch((int) $template->id, $subjectKind, (int) $id, [
                'certificate_type_id' => $template->certificate_type_id,
            ]);
        }

        $this->activity->record('documents.bulk_generated', "Bulk generation queued ({$scope})", $template, [
            'scope' => $scope, 'count' => count($ids),
        ], (int) $template->school_id, 'documents');

        return ['queued' => count($ids)];
    }

    /**
     * @param  array<string, mixed>  $target
     * @return array<int, int>
     */
    private function resolveSubjectIds(string $scope, array $target, string $subjectKind, int $schoolId): array
    {
        if ($subjectKind === 'staff') {
            return Staff::query()->where('school_id', $schoolId)
                ->when(isset($target['department_id']), fn ($q) => $q->where('department_id', $target['department_id']))
                ->where('status', 'active')->pluck('id')->map(fn ($v) => (int) $v)->all();
        }

        $records = StudentAcademicRecord::query()->where('school_id', $schoolId)->where('is_current', true)
            ->when($scope === 'class' && isset($target['class_id']), fn ($q) => $q->where('class_id', $target['class_id']))
            ->when($scope === 'section' && isset($target['section_id']), fn ($q) => $q->where('section_id', $target['section_id']))
            ->when($scope === 'academic_year' && isset($target['academic_year_id']), fn ($q) => $q->where('academic_year_id', $target['academic_year_id']))
            ->pluck('student_id');

        return Student::query()->whereIn('id', $records)->where('status', 'active')
            ->pluck('id')->map(fn ($v) => (int) $v)->all();
    }
}
