<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\MovementType;
use App\Modules\Inventory\Models\Consumable;
use App\Modules\Inventory\Models\StockMovement;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;

/**
 * Consumable stock engine. Movements are APPEND-ONLY — the running balance is
 * recomputed and snapshotted on each movement; quantities are never overwritten.
 * Crossing the minimum stock publishes a low-stock event (never sent directly).
 */
class StockService extends BaseService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly InventoryHooks $hooks,
    ) {}

    /**
     * @param  array{reference?:string|null, notes?:string|null}  $meta
     */
    public function record(Consumable $consumable, MovementType $type, float $quantity, array $meta = []): StockMovement
    {
        return $this->transaction(function () use ($consumable, $type, $quantity, $meta): StockMovement {
            $balance = $type->applyTo((float) $consumable->current_stock, $quantity);

            $movement = StockMovement::query()->create([
                'school_id' => $consumable->school_id,
                'consumable_id' => $consumable->id,
                'type' => $type->value,
                'quantity' => $quantity,
                'balance_after' => $balance,
                'reference' => $meta['reference'] ?? null,
                'notes' => $meta['notes'] ?? null,
                'moved_by' => Auth::id(),
                'created_at' => now(),
            ]);

            $consumable->update(['current_stock' => $balance]);

            $this->activity->record('inventory.stock_movement', "Stock {$type->value} for {$consumable->name}", $movement, [
                'quantity' => $quantity, 'balance_after' => $balance,
            ], (int) $consumable->school_id, 'inventory');

            if ($consumable->fresh()?->isLowStock()) {
                $this->hooks->lowStock((int) $consumable->school_id, "'{$consumable->name}' is at or below minimum stock ({$balance} {$consumable->unit}).");
            }

            return $movement;
        });
    }
}
