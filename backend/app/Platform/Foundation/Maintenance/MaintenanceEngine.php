<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Maintenance;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Foundation\Maintenance\Enums\MaintenanceStatus;
use App\Platform\Foundation\Maintenance\Models\MaintenanceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * The reusable Maintenance Engine. Any module registers preventive / corrective
 * / emergency maintenance against ANY maintainable (Inventory assets today;
 * Transport / Hostel / Laboratory / Facilities / IT assets in future) — the
 * engine owns scheduling, priority, assigned staff, cost, resolution, the Audit
 * Log and the Communication event. No workflow automation is implemented.
 */
class MaintenanceEngine
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly CommunicationEngine $communication,
    ) {}

    /**
     * Schedule a maintenance request against a maintainable model.
     *
     * @param  array<string, mixed>  $data
     */
    public function schedule(Model $maintainable, array $data): MaintenanceRequest
    {
        $request = MaintenanceRequest::query()->create([
            'school_id' => $data['school_id'] ?? $maintainable->getAttribute('school_id'),
            'maintainable_type' => $maintainable::class,
            'maintainable_id' => $maintainable->getKey(),
            'type' => $data['type'] ?? 'preventive',
            'priority' => $data['priority'] ?? 'medium',
            'assigned_staff_id' => $data['assigned_staff_id'] ?? null,
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'cost' => $data['cost'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'scheduled',
            'requested_by' => Auth::id(),
        ]);

        $this->activity->record('maintenance.scheduled', "Maintenance scheduled ({$request->type->value})", $request, [
            'maintainable_type' => class_basename($maintainable::class),
            'maintainable_id' => $maintainable->getKey(),
            'priority' => $request->priority->value,
        ], (int) $request->school_id, 'maintenance');

        $this->publish((int) $request->school_id, 'maintenance.due', 'Maintenance due',
            class_basename($maintainable::class).' #'.$maintainable->getKey()." has {$request->type->value} maintenance scheduled.");

        return $request;
    }

    /** Update a maintenance request (priority, staff, cost, notes, status). */
    public function update(MaintenanceRequest $request, array $data): MaintenanceRequest
    {
        $request->fill($data);

        if (($data['status'] ?? null) === MaintenanceStatus::Completed->value && $request->completed_date === null) {
            $request->completed_date = now()->toDateString();
        }

        $request->save();

        $this->activity->record('maintenance.updated', 'Maintenance updated', $request, [
            'status' => $request->status->value,
        ], (int) $request->school_id, 'maintenance');

        return $request->refresh();
    }

    private function publish(int $schoolId, string $event, string $subject, string $body): void
    {
        $this->communication->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Administrators,
            subject: $subject,
            body: $body,
            source: 'maintenance',
            event: $event,
        ));
    }
}
