<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Hostel\Enums\HostelGender;
use App\Modules\Hostel\Models\Hostel;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Hostels. Code is issued by the Number Generator when omitted. */
class HostelService extends BaseCrudService
{
    public function __construct(private readonly NumberGeneratorService $numbers) {}

    protected function model(): string
    {
        return Hostel::class;
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'gender', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return ['gender' => ['type' => 'enum', 'enum' => HostelGender::class]];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            if (empty($data['code'])) {
                $data['code'] = $this->numbers->next('hostel.code', (int) $data['school_id'], Auth::id());
            } else {
                $this->numbers->reserve('hostel.code', (string) $data['code'], (int) $data['school_id'], Auth::id());
            }

            return Hostel::query()->create($data);
        });
    }
}
