<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class SettingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'site_name' => ['nullable', 'string', 'max:255'],
            'logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'favicon_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'theme_colors' => ['nullable', 'array'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'social_links' => ['nullable', 'array'],
            'footer' => ['nullable', 'string'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'google_map' => ['nullable', 'string'],
            'homepage_config' => ['nullable', 'array'],
        ];
    }
}
