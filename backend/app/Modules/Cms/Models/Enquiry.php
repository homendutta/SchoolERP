<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\EnquiryStatus;
use Illuminate\Database\Eloquent\Model;

/** An admission enquiry captured from the public site (never auto-creates an admission). */
class Enquiry extends Model
{
    protected $table = 'cms_enquiries';

    protected $fillable = [
        'school_id', 'parent_name', 'student_name', 'interested_class', 'phone', 'email', 'notes', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'new'];

    protected function casts(): array
    {
        return ['status' => EnquiryStatus::class];
    }
}
