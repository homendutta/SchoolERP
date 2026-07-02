<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A vendor document (Media reference only). */
class VendorDocument extends Model
{
    protected $table = 'vendor_documents';

    protected $fillable = ['school_id', 'vendor_id', 'type', 'media_id', 'notes'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
