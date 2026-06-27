<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Resources;

use App\Modules\Administration\Models\School;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin School
 */
class SchoolResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'general' => [
                'name' => $this->name,
                'short_name' => $this->short_name,
                'code' => $this->code,
                'motto' => $this->motto,
                'about' => $this->about,
                'established_year' => $this->established_year,
                'registration_number' => $this->registration_number,
                'is_active' => (bool) $this->is_active,
            ],
            'branding' => $this->whenLoaded('branding', fn () => [
                'theme_color' => $this->branding->theme_color,
                'media' => $this->branding->urls(),
                'media_ids' => $this->branding->only([
                    'logo_media_id', 'logo_dark_media_id', 'favicon_media_id',
                    'login_background_media_id', 'principal_signature_media_id',
                    'stamp_media_id', 'report_logo_media_id', 'receipt_logo_media_id', 'id_card_media_id',
                ]),
            ]),
            'contact' => $this->whenLoaded('contact', fn () => $this->contact?->only([
                'email', 'phone', 'alt_phone', 'website', 'address', 'city', 'state', 'country', 'postal_code',
            ])),
            'regional' => $this->whenLoaded('regional', fn () => $this->regional?->only([
                'timezone', 'currency', 'locale', 'date_format', 'time_format', 'week_start',
            ])),
            'academic' => $this->whenLoaded('academic', fn () => $this->academic?->only([
                'academic_year', 'academic_year_start_month', 'session_label',
            ])),
        ];
    }
}
