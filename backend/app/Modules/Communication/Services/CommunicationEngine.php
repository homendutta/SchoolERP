<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Enums\MessageStatus;
use App\Modules\Communication\Models\ChannelSetting;
use App\Modules\Communication\Models\CommunicationBatch;
use App\Modules\Communication\Models\CommunicationMessage;
use App\Modules\Communication\Models\CommunicationTemplate;
use App\Modules\Communication\Models\DeliveryLog;
use App\Modules\Communication\Support\Channels\ProviderRegistry;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Carbon;

/**
 * The central Communication Engine — the single path for ALL messaging. It
 * resolves the template, resolves recipients, applies user preferences, queues
 * one message per recipient, dispatches via the channel provider registry,
 * retries failures using the configurable policy, and tracks every transition.
 * Business modules only publish requests; they never send directly.
 */
class CommunicationEngine extends BaseService
{
    public function __construct(
        private readonly RecipientResolver $recipients,
        private readonly TemplateRenderer $renderer,
        private readonly PreferenceService $preferences,
        private readonly ProviderRegistry $providers,
        private readonly ActivityLogger $activity,
    ) {}

    /** Publish a communication request → a batch of queued messages. */
    public function publish(CommunicationRequestData $request): CommunicationBatch
    {
        return $this->transaction(function () use ($request): CommunicationBatch {
            $template = $request->templateCode !== null ? $this->resolveTemplate($request) : null;
            $subject = $template?->subject ?? $request->subject;
            $body = $template?->body ?? $request->body ?? '';

            $batch = CommunicationBatch::query()->create([
                'school_id' => $request->schoolId,
                'template_id' => $template?->id,
                'source' => $request->source,
                'event' => $request->event,
                'channel' => $request->channel->value,
                'subject' => $subject,
                'body' => $body,
                'audience_type' => $request->audienceType->value,
                'class_id' => $request->classId,
                'section_id' => $request->sectionId,
                'department_id' => $request->departmentId,
                'is_mandatory' => $request->isMandatory,
                'scheduled_at' => $request->scheduledAt,
                'created_by' => $request->createdBy,
            ]);

            $count = 0;
            foreach ($this->recipients->resolve($request) as $recipient) {
                $this->queueMessage($request, $batch, $subject, $body, $recipient);
                $count++;
            }

            $batch->update(['total_recipients' => $count, 'status' => $request->scheduledAt !== null ? 'scheduled' : 'queued']);

            $this->activity->record('communication.published', "Published {$count} message(s) via {$request->channel->value}", $batch, [
                'channel' => $request->channel->value, 'audience' => $request->audienceType->value, 'recipients' => $count,
            ], $request->schoolId, 'communication');

            return $batch;
        });
    }

    /**
     * @param  array{recipient_type:string, recipient_id:int, recipient_name:string, email:?string, phone:?string, user_id:?int}  $recipient
     */
    private function queueMessage(CommunicationRequestData $request, CommunicationBatch $batch, ?string $subject, string $body, array $recipient): void
    {
        $vars = $request->variables + ['recipient_name' => $recipient['recipient_name']];
        $address = $this->addressFor($request->channel, $recipient);
        $setting = $this->setting($request->schoolId, $request->channel);

        // Respect user preferences unless the message is mandatory.
        $allowed = $request->isMandatory || $this->preferences->isEnabled($recipient['user_id'], $request->channel);

        $message = CommunicationMessage::query()->create([
            'school_id' => $request->schoolId,
            'batch_id' => $batch->id,
            'template_id' => $batch->template_id,
            'channel' => $request->channel->value,
            'recipient_type' => $recipient['recipient_type'],
            'recipient_id' => $recipient['recipient_id'],
            'recipient_name' => $recipient['recipient_name'],
            'user_id' => $recipient['user_id'],
            'address' => $address,
            'subject' => $this->renderer->render($subject, $vars),
            'body' => $this->renderer->render($body, $vars),
            'status' => $allowed ? MessageStatus::Pending->value : MessageStatus::Cancelled->value,
            'is_mandatory' => $request->isMandatory,
            'scheduled_at' => $request->scheduledAt,
            'max_attempts' => $setting->max_attempts,
            'created_by' => $request->createdBy,
        ]);

        $this->log($message, 'created');
        $this->log($message, $allowed ? 'queued' : 'cancelled', $allowed ? null : 'user preference (opt-out)');
    }

    /** Process due pending messages (the queue worker). Never loses messages. */
    public function processDue(?int $schoolId = null, int $limit = 200): int
    {
        $now = Carbon::now();
        $messages = CommunicationMessage::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', MessageStatus::Pending->value)
            ->where(fn ($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', $now))
            ->limit($limit)
            ->get();

        $processed = 0;
        foreach ($messages as $message) {
            $this->processMessage($message);
            $processed++;
        }

        return $processed;
    }

    public function processMessage(CommunicationMessage $message): CommunicationMessage
    {
        $channel = $message->channel;
        $provider = $this->providers->get($channel);

        $message->update(['status' => MessageStatus::Processing->value, 'attempts' => $message->attempts + 1]);

        $ok = false;
        $error = null;
        try {
            $ok = $provider !== null && $provider->send($message);
            if ($provider === null) {
                $error = "No provider registered for channel '{$channel->value}'.";
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if ($ok) {
            $message->update([
                'status' => MessageStatus::Delivered->value,
                'sent_at' => now(),
                'delivered_at' => now(),
                'provider' => $provider?->channel()->value,
                'error' => null,
            ]);
            $this->log($message, 'sent');
            $this->log($message, 'delivered');

            return $message->refresh();
        }

        return $this->handleFailure($message, $error ?? 'Delivery failed.');
    }

    private function handleFailure(CommunicationMessage $message, string $error): CommunicationMessage
    {
        $setting = $this->setting((int) $message->school_id, $message->channel);

        if ($message->attempts < $message->max_attempts) {
            $delay = $setting->backoff->delayFor($message->attempts, $setting->retry_delay_seconds);
            $message->update([
                'status' => MessageStatus::Pending->value,
                'next_attempt_at' => now()->addSeconds($delay),
                'error' => $error,
            ]);
            $this->log($message, 'retried', "attempt {$message->attempts}: {$error}");
        } else {
            $message->update(['status' => MessageStatus::Failed->value, 'failed_at' => now(), 'error' => $error]);
            $this->log($message, 'failed', $error);
        }

        return $message->refresh();
    }

    /** Manually re-queue a failed/cancelled message (never lost). */
    public function retry(CommunicationMessage $message): CommunicationMessage
    {
        $message->update(['status' => MessageStatus::Pending->value, 'next_attempt_at' => null, 'failed_at' => null]);
        $this->log($message, 'retried', 'manual');

        return $message->refresh();
    }

    public function markRead(CommunicationMessage $message): CommunicationMessage
    {
        $message->update(['status' => MessageStatus::Read->value, 'read_at' => now()]);
        $this->log($message, 'read');

        return $message->refresh();
    }

    public function cancel(CommunicationMessage $message): CommunicationMessage
    {
        $message->update(['status' => MessageStatus::Cancelled->value]);
        $this->log($message, 'cancelled');

        return $message->refresh();
    }

    private function resolveTemplate(CommunicationRequestData $request): ?CommunicationTemplate
    {
        return CommunicationTemplate::query()
            ->where('school_id', $request->schoolId)
            ->where('code', $request->templateCode)
            ->where('channel', $request->channel->value)
            ->where('status', 'active')
            ->first();
    }

    /**
     * @param  array{email:?string, phone:?string}  $recipient
     */
    private function addressFor(CommunicationChannel $channel, array $recipient): ?string
    {
        return match ($channel) {
            CommunicationChannel::Email => $recipient['email'],
            CommunicationChannel::InApp => null,
            default => $recipient['phone'], // sms / push / whatsapp / voice …
        };
    }

    private function setting(int $schoolId, CommunicationChannel $channel): ChannelSetting
    {
        return ChannelSetting::query()->firstOrNew(
            ['school_id' => $schoolId, 'channel' => $channel->value],
        );
    }

    private function log(CommunicationMessage $message, string $event, ?string $detail = null): void
    {
        DeliveryLog::query()->create([
            'message_id' => $message->id,
            'event' => $event,
            'detail' => $detail,
            'created_at' => now(),
        ]);
    }
}
