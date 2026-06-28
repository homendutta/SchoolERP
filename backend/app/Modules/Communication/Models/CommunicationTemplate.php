<?php

declare(strict_types=1);

namespace App\Modules\Communication\Models;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A reusable, variable-driven message template. */
class CommunicationTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'communication_templates';

    protected $fillable = [
        'school_id', 'name', 'code', 'channel', 'subject', 'body', 'variables', 'language', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'language' => 'en', 'channel' => 'email'];

    protected function casts(): array
    {
        return [
            'channel' => CommunicationChannel::class,
            'variables' => 'array',
            'status' => RecordStatus::class,
        ];
    }
}
