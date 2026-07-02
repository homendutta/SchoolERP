<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Transport\Models\TransportRoute;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Routes. Route code is issued by the Number Generator when omitted. */
class RouteService extends BaseCrudService
{
    public function __construct(private readonly NumberGeneratorService $numbers) {}

    protected function model(): string
    {
        return TransportRoute::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->withCount('stops');
    }

    protected function searchable(): array
    {
        return ['name', 'route_code', 'start_location', 'end_location'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'route_code'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            if (empty($data['route_code'])) {
                $data['route_code'] = $this->numbers->next('transport.route', (int) $data['school_id'], Auth::id());
            } else {
                $this->numbers->reserve('transport.route', (string) $data['route_code'], (int) $data['school_id'], Auth::id());
            }

            return TransportRoute::query()->create($data);
        });
    }

    public function find(int|string $id): Model
    {
        return TransportRoute::query()->with('stops')->findOrFail($id);
    }
}
