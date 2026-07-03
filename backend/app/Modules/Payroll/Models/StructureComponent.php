<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A component attached to a salary structure (with an optional value override). */
class StructureComponent extends Model
{
    protected $table = 'payroll_structure_components';

    protected $fillable = ['structure_id', 'component_id', 'value', 'sequence'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'sequence' => 'integer',
        ];
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class, 'structure_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id');
    }
}
