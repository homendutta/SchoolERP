<?php

declare(strict_types=1);

namespace App\Modules\Students\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentWithdrawal extends Model
{
    protected $table = 'student_withdrawals';

    protected $fillable = [
        'school_id', 'student_id', 'withdraw_date', 'reason', 'approved_by', 'remarks',
    ];

    protected function casts(): array
    {
        return ['withdraw_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
