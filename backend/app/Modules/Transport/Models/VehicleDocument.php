<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use App\Modules\Transport\Enums\DocumentType;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A vehicle document (Media reference only). */
class VehicleDocument extends Model
{
    use SoftDeletes;

    protected $table = 'transport_vehicle_documents';

    protected $fillable = [
        'school_id', 'vehicle_id', 'document_type', 'media_id', 'number', 'issue_date', 'expiry_date', 'notes',
    ];

    protected function casts(): array
    {
        return ['document_type' => DocumentType::class, 'issue_date' => 'date', 'expiry_date' => 'date'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
