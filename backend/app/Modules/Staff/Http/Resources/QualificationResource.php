<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Resources;

use App\Modules\Staff\Models\StaffQualification;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StaffQualification
 */
class QualificationResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'qualification' => $this->qualification,
            'institution' => $this->institution,
            'board_university' => $this->board_university,
            'year' => $this->year,
            'grade' => $this->grade,
            'certificate_media_id' => $this->certificate_media_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
