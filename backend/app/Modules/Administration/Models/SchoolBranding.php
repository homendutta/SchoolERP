<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolBranding extends Model
{
    use InteractsWithMedia;

    protected $table = 'school_branding';

    protected $fillable = [
        'school_id', 'theme_color',
        'logo_media_id', 'logo_dark_media_id', 'favicon_media_id',
        'login_background_media_id', 'principal_signature_media_id',
        'stamp_media_id', 'report_logo_media_id', 'receipt_logo_media_id',
        'id_card_media_id',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** Resolve all branding media ids to public URLs. */
    public function urls(): array
    {
        return [
            'logo' => $this->mediaUrl($this->logo_media_id),
            'logo_dark' => $this->mediaUrl($this->logo_dark_media_id),
            'favicon' => $this->mediaUrl($this->favicon_media_id),
            'login_background' => $this->mediaUrl($this->login_background_media_id),
            'principal_signature' => $this->mediaUrl($this->principal_signature_media_id),
            'stamp' => $this->mediaUrl($this->stamp_media_id),
            'report_logo' => $this->mediaUrl($this->report_logo_media_id),
            'receipt_logo' => $this->mediaUrl($this->receipt_logo_media_id),
            'id_card_logo' => $this->mediaUrl($this->id_card_media_id),
        ];
    }
}
