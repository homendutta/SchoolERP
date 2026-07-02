<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Facades\Log;

/**
 * Transport → Communication integration + boarding/alighting event hooks.
 * Transport NEVER sends notifications itself; each hook only publishes a
 * communication request. Boarding/alighting hooks publish events only (no
 * transport attendance), ready for future QR / RFID / face-recognition sources.
 */
class TransportHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function routeChanged(int $schoolId, Student $student, string $routeName): void
    {
        $this->notify($schoolId, 'transport.route_changed', $student, 'Transport route changed', "Route updated to {$routeName}.");
    }

    public function driverChanged(int $schoolId, Student $student, string $driverName): void
    {
        $this->notify($schoolId, 'transport.driver_changed', $student, 'Transport driver changed', "New driver: {$driverName}.");
    }

    public function vehicleChanged(int $schoolId, Student $student, string $vehicleNumber): void
    {
        $this->notify($schoolId, 'transport.vehicle_changed', $student, 'Transport vehicle changed', "New vehicle: {$vehicleNumber}.");
    }

    public function delayNotice(int $schoolId, Student $student, string $detail): void
    {
        $this->notify($schoolId, 'transport.delay_notice', $student, 'Transport delay', $detail);
    }

    public function pickupReminder(int $schoolId, Student $student, string $stopName, string $time): void
    {
        $this->notify($schoolId, 'transport.pickup_reminder', $student, 'Pickup reminder', "Pickup at {$stopName} around {$time}.");
    }

    /**
     * Boarding/alighting hook — publishes an event only (future QR/RFID/face).
     * No transport attendance is recorded here.
     *
     * @param  array<string, scalar|null>  $context
     */
    public function boardingEvent(int $schoolId, string $direction, array $context = []): void
    {
        // Publish an event only; future sources (QR/RFID/face) and consumers wire
        // in here without changing the module.
        Log::info('transport.boarding', ['school_id' => $schoolId, 'direction' => $direction, 'context' => $context]);
    }

    private function notify(int $schoolId, string $event, Student $student, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Custom,
            subject: $subject,
            body: $body,
            source: 'transport',
            event: $event,
            recipients: [[
                'recipient_type' => Student::class,
                'recipient_id' => $student->id,
                'recipient_name' => (string) $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'user_id' => $student->user_id,
            ]],
        ));
    }
}
