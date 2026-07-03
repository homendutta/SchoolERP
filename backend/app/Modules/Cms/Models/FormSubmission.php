<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\EnquiryStatus;
use App\Modules\Cms\Enums\FormType;
use Illuminate\Database\Eloquent\Model;

/** A public form submission captured into the ERP (Communication notifies staff). */
class FormSubmission extends Model
{
    protected $table = 'cms_form_submissions';

    protected $fillable = [
        'school_id', 'form_id', 'type', 'name', 'email', 'phone', 'subject', 'message', 'payload', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'new', 'type' => 'contact'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'type' => FormType::class, 'status' => EnquiryStatus::class];
    }
}
