<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;
use Illuminate\Database\Eloquent\Model;

/**
 * Library → Communication integration. Library NEVER sends messages itself; each
 * hook only publishes a communication request through the engine (no
 * notification logic embedded here).
 */
class LibraryHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function dueReminder(int $schoolId, Model $borrower, string $bookTitle, string $dueDate): void
    {
        $this->publish($schoolId, 'library.due_reminder', $borrower, 'Library book due', "'{$bookTitle}' is due on {$dueDate}.");
    }

    public function overdueReminder(int $schoolId, Model $borrower, string $bookTitle, float $fine): void
    {
        $this->publish($schoolId, 'library.overdue_reminder', $borrower, 'Library book overdue', "'{$bookTitle}' is overdue. Fine so far: {$fine}.");
    }

    public function reservationAvailable(int $schoolId, Model $borrower, string $bookTitle): void
    {
        $this->publish($schoolId, 'library.reservation_available', $borrower, 'Reserved book available', "Your reserved book '{$bookTitle}' is now available to collect.");
    }

    public function lostBookNotice(int $schoolId, Model $borrower, string $bookTitle): void
    {
        $this->publish($schoolId, 'library.lost_book', $borrower, 'Lost library book', "'{$bookTitle}' has been marked lost against your account.");
    }

    private function publish(int $schoolId, string $event, Model $borrower, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Custom,
            subject: $subject,
            body: $body,
            source: 'library',
            event: $event,
            recipients: [[
                'recipient_type' => $borrower::class,
                'recipient_id' => $borrower->getKey(),
                'recipient_name' => (string) $borrower->getAttribute('name'),
                'email' => $borrower->getAttribute('email'),
                'phone' => $borrower->getAttribute('phone'),
                'user_id' => $borrower->getAttribute('user_id'),
            ]],
        ));
    }
}
