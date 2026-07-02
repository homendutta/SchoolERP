<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use App\Modules\Library\Enums\BorrowStatus;
use App\Platform\Foundation\Identity\Models\Identity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** A borrowing transaction against a physical copy. */
class Borrowing extends Model
{
    protected $table = 'library_borrowings';

    protected $fillable = [
        'school_id', 'identity_id', 'owner_type', 'owner_id', 'copy_id', 'book_id',
        'borrow_date', 'due_date', 'return_date', 'status', 'renewals_count',
        'late_days', 'fine_amount', 'fine_waived', 'damage_notes', 'issued_by', 'returned_to',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'borrowed', 'renewals_count' => 0, 'late_days' => 0, 'fine_amount' => 0, 'fine_waived' => false];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'status' => BorrowStatus::class,
            'renewals_count' => 'integer',
            'late_days' => 'integer',
            'fine_amount' => 'float',
            'fine_waived' => 'boolean',
        ];
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(Copy::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(Renewal::class)->latest('id');
    }
}
