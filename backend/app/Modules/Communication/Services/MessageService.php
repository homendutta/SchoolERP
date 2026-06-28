<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Enums\MessageStatus;
use App\Modules\Communication\Models\CommunicationMessage;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + search over the message queue + delivery tracking. */
class MessageService extends BaseCrudService
{
    protected function model(): string
    {
        return CommunicationMessage::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['logs:id,message_id,event,detail,created_at']);
    }

    protected function searchable(): array
    {
        return ['recipient_name', 'address', 'subject'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'batch_id', 'channel', 'status', 'recipient_type', 'is_mandatory'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at', 'scheduled_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'channel' => ['type' => 'enum', 'enum' => CommunicationChannel::class],
            'status' => ['type' => 'enum', 'enum' => MessageStatus::class],
            'created_at' => ['type' => 'date'],
        ];
    }

    /** Scheduled (future) messages only. */
    public function scheduled(int $schoolId): Builder
    {
        return CommunicationMessage::query()
            ->where('school_id', $schoolId)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->where('status', MessageStatus::Pending->value);
    }
}
