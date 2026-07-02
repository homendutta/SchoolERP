<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Transport\Enums\VehicleStatus;
use App\Modules\Transport\Enums\VehicleType;
use App\Modules\Transport\Models\Vehicle;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Vehicles. Vehicle number is issued by the Number Generator when omitted. */
class VehicleService extends BaseCrudService
{
    public function __construct(private readonly NumberGeneratorService $numbers) {}

    protected function model(): string
    {
        return Vehicle::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['documents:id,vehicle_id,document_type,expiry_date', 'staff:id,vehicle_id,staff_id,role']);
    }

    protected function searchable(): array
    {
        return ['vehicle_number', 'registration_number', 'model', 'manufacturer'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'vehicle_type', 'fuel_type', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'vehicle_number', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'vehicle_number' => ['type' => 'text', 'columns' => ['vehicle_number']],
            'registration' => ['type' => 'text', 'columns' => ['registration_number']],
            'status' => ['type' => 'enum', 'enum' => VehicleStatus::class],
            'vehicle_type' => ['type' => 'enum', 'enum' => VehicleType::class],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            if (empty($data['vehicle_number'])) {
                $data['vehicle_number'] = $this->numbers->next('transport.vehicle', (int) $data['school_id'], Auth::id());
            } else {
                $this->numbers->reserve('transport.vehicle', (string) $data['vehicle_number'], (int) $data['school_id'], Auth::id());
            }

            return Vehicle::query()->create($data);
        });
    }
}
