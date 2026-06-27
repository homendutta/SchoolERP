<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Business Number Registry entry — one row per issued official number, for
 * uniqueness guarantees and history.
 */
class BusinessNumber extends Model
{
    protected $table = 'business_number_registry';

    protected $fillable = ['school_id', 'type', 'number', 'sequence_id', 'issued_by', 'generated_at'];

    protected function casts(): array
    {
        return ['generated_at' => 'datetime'];
    }
}
