<?php

declare(strict_types=1);

namespace App\Modules\Communication\Models;

use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A published communication request that fans out to many messages. */
class CommunicationBatch extends Model
{
    protected $table = 'communication_batches';

    protected $fillable = [
        'school_id', 'template_id', 'source', 'event', 'channel', 'subject', 'body',
        'audience_type', 'class_id', 'section_id', 'department_id',
        'is_mandatory', 'scheduled_at', 'status', 'total_recipients', 'created_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending', 'total_recipients' => 0];

    protected function casts(): array
    {
        return [
            'channel' => CommunicationChannel::class,
            'audience_type' => AudienceType::class,
            'is_mandatory' => 'boolean',
            'scheduled_at' => 'datetime',
            'total_recipients' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class, 'template_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CommunicationMessage::class, 'batch_id');
    }
}
