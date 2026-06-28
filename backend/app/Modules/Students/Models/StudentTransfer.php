<?php

declare(strict_types=1);

namespace App\Modules\Students\Models;

use App\Modules\Students\Enums\TransferType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransfer extends Model
{
    protected $table = 'student_transfers';

    protected $fillable = [
        'school_id', 'student_id', 'type', 'academic_year_id',
        'from_class_id', 'from_section_id', 'to_class_id', 'to_section_id',
        'transfer_date', 'reason', 'destination_school', 'notes', 'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransferType::class,
            'transfer_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
