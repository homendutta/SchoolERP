<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;

/** Website settings — one row per school (the public site's global config). */
class Setting extends Model
{
    use InteractsWithMedia;

    protected $table = 'cms_settings';

    protected $fillable = [
        'school_id', 'site_name', 'logo_media_id', 'favicon_media_id', 'theme_colors',
        'email', 'phone', 'address', 'social_links', 'footer', 'copyright', 'google_map', 'homepage_config',
    ];

    protected function casts(): array
    {
        return [
            'theme_colors' => 'array',
            'social_links' => 'array',
            'homepage_config' => 'array',
        ];
    }
}
