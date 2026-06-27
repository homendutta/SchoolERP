<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolContact extends Model
{
    protected $table = 'school_contact';

    protected $fillable = [
        'school_id', 'email', 'phone', 'alt_phone', 'website',
        'address', 'city', 'state', 'country', 'postal_code',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
