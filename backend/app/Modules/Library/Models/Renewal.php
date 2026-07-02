<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A renewal event — preserves due-date extension history. */
class Renewal extends Model
{
    protected $table = 'library_renewals';

    protected $fillable = ['borrowing_id', 'renewed_on', 'previous_due_date', 'new_due_date', 'renewed_by'];

    protected function casts(): array
    {
        return ['renewed_on' => 'date', 'previous_due_date' => 'date', 'new_due_date' => 'date'];
    }

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class);
    }
}
