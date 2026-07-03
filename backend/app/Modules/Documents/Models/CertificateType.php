<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A configurable certificate type (Transfer, Bonafide, Salary, ...). */
class CertificateType extends Model
{
    use SoftDeletes;

    protected $table = 'document_certificate_types';

    protected $fillable = [
        'school_id', 'category_id', 'name', 'code', 'default_template_id', 'subject_kind', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'subject_kind' => 'student'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
