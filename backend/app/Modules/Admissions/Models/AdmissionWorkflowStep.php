<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A configurable step in the school's admission approval workflow definition.
 */
class AdmissionWorkflowStep extends Model
{
    protected $fillable = [
        'school_id', 'name', 'role_slug', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
