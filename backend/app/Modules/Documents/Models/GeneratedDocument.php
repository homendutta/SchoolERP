<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Modules\Documents\Enums\DocumentStatus;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Traits\HasIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An IMMUTABLE generated document. It carries a permanent platform Identity whose
 * identity_number / public_identifier drive QR + public verification (QR images
 * are produced dynamically, never stored). Regeneration creates a new row.
 */
class GeneratedDocument extends Model
{
    use HasIdentity;

    protected $table = 'generated_documents';

    protected $fillable = [
        'school_id', 'document_number', 'certificate_type_id', 'template_id', 'template_version',
        'subject_type', 'subject_id', 'identity_id', 'verification_code', 'rendered_html', 'variables',
        'signatures', 'version', 'parent_id', 'status', 'issued_by', 'issued_to', 'issue_date', 'remarks',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'generated', 'version' => 1];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'signatures' => 'array',
            'version' => 'integer',
            'template_version' => 'integer',
            'issue_date' => 'date',
            'status' => DocumentStatus::class,
        ];
    }

    public function identityType(): IdentityType
    {
        return IdentityType::Document;
    }

    public function documentIdentity(): BelongsTo
    {
        return $this->belongsTo(Identity::class, 'identity_id');
    }

    public function certificateType(): BelongsTo
    {
        return $this->belongsTo(CertificateType::class, 'certificate_type_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
