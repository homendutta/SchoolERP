<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Enums\CopyStatus;
use App\Modules\Library\Models\Copy;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Physical copies. On create the HasIdentity trait issues the copy's permanent
 * Identity (barcode + QR come from that Identity).
 */
class CopyService extends BaseCrudService
{
    protected function model(): string
    {
        return Copy::class;
    }

    /** Eager-load relations on single-record reads (show). */
    public function find(int|string $id): Model
    {
        $query = $this->query();
        $this->withRelations($query);

        return $query->findOrFail($id);
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'book:id,title,isbn',
            'location:id,name,room,rack,shelf',
            'copyIdentity:id,identity_number,public_identifier,barcode_value,qr_payload',
        ]);
    }

    protected function searchable(): array
    {
        return ['copy_number'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'book_id', 'location_id', 'status', 'condition'];
    }

    protected function sortable(): array
    {
        return ['id', 'copy_number', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'copy_number' => ['type' => 'text', 'columns' => ['copy_number']],
            'status' => ['type' => 'enum', 'enum' => CopyStatus::class],
            'barcode' => ['type' => 'relation', 'relation' => 'copyIdentity', 'columns' => ['identity_number', 'barcode_value']],
        ];
    }
}
