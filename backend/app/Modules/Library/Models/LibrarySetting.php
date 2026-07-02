<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;

/** Per-school circulation policy (borrow periods, renewals, reservation expiry). */
class LibrarySetting extends Model
{
    protected $table = 'library_settings';

    protected $fillable = [
        'school_id', 'student_borrow_days', 'staff_borrow_days',
        'max_renewals', 'max_books_per_borrower', 'reservation_expiry_days',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'student_borrow_days' => 14, 'staff_borrow_days' => 30,
        'max_renewals' => 2, 'max_books_per_borrower' => 3, 'reservation_expiry_days' => 3,
    ];

    protected function casts(): array
    {
        return [
            'student_borrow_days' => 'integer',
            'staff_borrow_days' => 'integer',
            'max_renewals' => 'integer',
            'max_books_per_borrower' => 'integer',
            'reservation_expiry_days' => 'integer',
        ];
    }
}
