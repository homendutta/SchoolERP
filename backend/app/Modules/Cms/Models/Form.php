<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\FormType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A dynamic public form definition (contact / admission enquiry / general enquiry). */
class Form extends Model
{
    use SoftDeletes;

    protected $table = 'cms_forms';

    protected $fillable = ['school_id', 'name', 'type', 'fields', 'notify_email', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'type' => 'contact'];

    protected function casts(): array
    {
        return ['fields' => 'array', 'type' => FormType::class, 'status' => RecordStatus::class];
    }
}
