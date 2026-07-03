<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Modules\Documents\Enums\Orientation;
use App\Modules\Documents\Enums\PaperSize;
use App\Platform\Enums\RecordStatus;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A versioned document template. A new version is a new row (parent_id links back). */
class Template extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'document_templates';

    protected $fillable = [
        'school_id', 'category_id', 'certificate_type_id', 'name', 'code', 'version', 'parent_id',
        'html', 'header', 'footer', 'variables', 'logo_media_id', 'watermark_media_id',
        'background_media_id', 'margins', 'orientation', 'paper_size', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'version' => 1, 'orientation' => 'portrait', 'paper_size' => 'a4'];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'margins' => 'array',
            'version' => 'integer',
            'orientation' => Orientation::class,
            'paper_size' => PaperSize::class,
            'status' => RecordStatus::class,
        ];
    }

    public function certificateType(): BelongsTo
    {
        return $this->belongsTo(CertificateType::class, 'certificate_type_id');
    }
}
