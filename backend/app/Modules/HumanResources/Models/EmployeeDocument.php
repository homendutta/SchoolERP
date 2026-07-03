<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\Staff\Models\Staff;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Employee document — stores ONLY a Media reference (Media Platform owns files). */
class EmployeeDocument extends Model
{
    protected $table = 'hr_employee_documents';

    protected $fillable = [
        'school_id', 'staff_id', 'document_type', 'media_id', 'title',
        'issued_date', 'expiry_date', 'remarks', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
