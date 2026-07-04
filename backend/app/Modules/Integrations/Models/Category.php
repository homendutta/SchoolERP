<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;

/** A configurable integration category (Authentication, Payment, Communication, ...). */
class Category extends Model
{
    protected $table = 'integration_categories';

    protected $fillable = ['school_id', 'name', 'code', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }
}
