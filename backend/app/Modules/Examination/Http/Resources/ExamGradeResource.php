<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Resources;

use App\Modules\Examination\Models\ExamGrade;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ExamGrade
 */
class ExamGradeResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'code' => $this->code,
            'name' => $this->name,
            'min_percentage' => $this->min_percentage,
            'max_percentage' => $this->max_percentage,
            'grade_point' => $this->grade_point,
            'remarks' => $this->remarks,
            'is_failing' => $this->is_failing,
            'sort_order' => $this->sort_order,
            'status' => $this->status->value,
        ];
    }
}
