<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Resources;

use App\Modules\Communication\Models\CommunicationTemplate;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin CommunicationTemplate
 */
class TemplateResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'code' => $this->code,
            'channel' => $this->channel->value,
            'subject' => $this->subject,
            'body' => $this->body,
            'variables' => $this->variables ?? [],
            'language' => $this->language,
            'status' => $this->status->value,
        ];
    }
}
