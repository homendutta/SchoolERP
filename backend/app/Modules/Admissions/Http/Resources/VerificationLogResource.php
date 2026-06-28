<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Resources;

use App\Modules\Admissions\Models\AdmissionVerificationLog;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin AdmissionVerificationLog
 */
class VerificationLogResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'document_id' => $this->document_id,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'remarks' => $this->remarks,
            'actor_id' => $this->actor_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
