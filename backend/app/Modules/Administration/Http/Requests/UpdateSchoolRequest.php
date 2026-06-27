<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class UpdateSchoolRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true; // route enforces permission:school.update
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'general' => ['sometimes', 'array'],
            'general.name' => ['sometimes', 'string', 'max:255'],
            'general.short_name' => ['nullable', 'string', 'max:255'],
            'general.motto' => ['nullable', 'string', 'max:255'],
            'general.about' => ['nullable', 'string'],
            'general.established_year' => ['nullable', 'string', 'max:8'],
            'general.registration_number' => ['nullable', 'string', 'max:255'],
            'general.is_active' => ['sometimes', 'boolean'],

            'branding' => ['sometimes', 'array'],
            'branding.theme_color' => ['sometimes', 'string', 'max:16'],
            'branding.logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'branding.logo_dark_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'branding.favicon_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'branding.login_background_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'branding.principal_signature_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'branding.stamp_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'branding.report_logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'branding.receipt_logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'branding.id_card_media_id' => ['nullable', 'integer', 'exists:media,id'],

            'contact' => ['sometimes', 'array'],
            'contact.email' => ['nullable', 'email', 'max:255'],
            'contact.phone' => ['nullable', 'string', 'max:32'],
            'contact.alt_phone' => ['nullable', 'string', 'max:32'],
            'contact.website' => ['nullable', 'string', 'max:255'],
            'contact.address' => ['nullable', 'string', 'max:255'],
            'contact.city' => ['nullable', 'string', 'max:120'],
            'contact.state' => ['nullable', 'string', 'max:120'],
            'contact.country' => ['nullable', 'string', 'max:120'],
            'contact.postal_code' => ['nullable', 'string', 'max:16'],

            'regional' => ['sometimes', 'array'],
            'regional.timezone' => ['sometimes', 'string', 'max:64'],
            'regional.currency' => ['sometimes', 'string', 'max:8'],
            'regional.locale' => ['sometimes', 'string', 'max:8'],
            'regional.date_format' => ['sometimes', 'string', 'max:20'],
            'regional.time_format' => ['sometimes', 'string', 'max:12'],
            'regional.week_start' => ['sometimes', 'string', 'max:12'],

            'academic' => ['sometimes', 'array'],
            'academic.academic_year' => ['nullable', 'string', 'max:32'],
            'academic.academic_year_start_month' => ['sometimes', 'integer', 'between:1,12'],
            'academic.session_label' => ['nullable', 'string', 'max:64'],
        ];
    }
}
