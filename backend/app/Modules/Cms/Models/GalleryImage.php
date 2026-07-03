<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One image in a gallery album (Media reference + caption + order). */
class GalleryImage extends Model
{
    use InteractsWithMedia;

    protected $table = 'cms_gallery_images';

    protected $fillable = ['gallery_id', 'media_id', 'caption', 'sequence'];

    protected function casts(): array
    {
        return ['sequence' => 'integer'];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class, 'gallery_id');
    }
}
