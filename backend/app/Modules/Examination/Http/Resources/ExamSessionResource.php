<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Resources;

use App\Modules\Examination\Models\ExamSession;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ExamSession
 */
class ExamSessionResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'academic_year_id' => $this->academic_year_id,
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear?->name),
            'term_id' => $this->term_id,
            'term' => $this->whenLoaded('term', fn () => $this->term?->name),
            'exam_type_id' => $this->exam_type_id,
            'exam_type' => $this->whenLoaded('examType', fn () => $this->examType?->name),
            'name' => $this->name,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status->value,
            'ranking_method' => $this->ranking_method->value,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
