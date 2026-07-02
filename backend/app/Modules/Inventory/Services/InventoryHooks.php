<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;

/**
 * Inventory → Communication integration. Inventory NEVER sends notifications
 * itself; each hook only publishes a communication request (to administrators)
 * through the engine.
 */
class InventoryHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function warrantyExpiring(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'inventory.warranty_expiring', 'Warranty expiring', $detail);
    }

    public function maintenanceDue(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'inventory.maintenance_due', 'Asset maintenance due', $detail);
    }

    public function lowStock(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'inventory.low_stock', 'Low stock', $detail);
    }

    public function verificationDue(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'inventory.verification_due', 'Verification due', $detail);
    }

    public function assetAssigned(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'inventory.asset_assigned', 'Asset assigned', $detail);
    }

    public function assetReturned(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'inventory.asset_returned', 'Asset returned', $detail);
    }

    private function notify(int $schoolId, string $event, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Administrators,
            subject: $subject,
            body: $body,
            source: 'inventory',
            event: $event,
        ));
    }
}
