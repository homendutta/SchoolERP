<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use App\Modules\Library\Enums\CopyCondition;
use App\Modules\Library\Enums\CopyStatus;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Traits\HasIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A physical, borrowable copy. Every copy gets its own permanent platform
 * Identity on creation (barcode value + QR payload come from the Identity
 * Platform, generated dynamically — never stored as images).
 */
class Copy extends Model
{
    use HasIdentity;
    use SoftDeletes;

    protected $table = 'library_copies';

    protected $fillable = [
        'school_id', 'book_id', 'identity_id', 'copy_number', 'location_id', 'shelf', 'rack',
        'acquisition_date', 'purchase_price', 'condition', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'available', 'condition' => 'good'];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'purchase_price' => 'float',
            'condition' => CopyCondition::class,
            'status' => CopyStatus::class,
        ];
    }

    public function identityType(): IdentityType
    {
        return IdentityType::LibraryCopy;
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(LibraryLocation::class, 'location_id');
    }

    /** The copy's own Identity (source of barcode + QR). */
    public function copyIdentity(): BelongsTo
    {
        return $this->belongsTo(Identity::class, 'identity_id');
    }
}
