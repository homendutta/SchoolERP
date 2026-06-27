<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Maps the reserved word "class" to the `classes` table. */
class SchoolClass extends Model
{
    use SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'school_id', 'code', 'name', 'short_name', 'slug', 'display_order', 'status', 'version',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'version' => 'integer',
            'status' => RecordStatus::class,
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id')->orderBy('display_order');
    }
}
