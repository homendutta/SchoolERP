<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Resources;

use App\Modules\Staff\Models\StaffExperience;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StaffExperience
 */
class ExperienceResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'organization' => $this->organization,
            'designation' => $this->designation,
            'from_date' => $this->from_date?->toDateString(),
            'to_date' => $this->to_date?->toDateString(),
            'reason_for_leaving' => $this->reason_for_leaving,
            'certificate_media_id' => $this->certificate_media_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
