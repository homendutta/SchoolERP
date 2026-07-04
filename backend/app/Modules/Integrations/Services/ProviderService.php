<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Integrations\Enums\ProviderStatus;
use App\Modules\Integrations\Models\Provider;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Integration providers. Configuration is stored encrypted; every configuration
 * change is audited and recorded on the Timeline. Only one provider per category
 * may be the default.
 */
class ProviderService extends BaseCrudService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    protected function model(): string
    {
        return Provider::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('category:id,name,code');
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'category_id', 'status', 'health'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'priority'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'name' => ['type' => 'text', 'columns' => ['name', 'code']],
            'status' => ['type' => 'enum', 'enum' => ProviderStatus::class],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $this->clearDefault($data);
            $provider = Provider::query()->create($data);
            $this->activity->record('integrations.provider_created', "Provider {$provider->name} registered", $provider, [], (int) $provider->school_id, 'integrations');

            return $provider;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        return $this->transaction(function () use ($model, $data): Model {
            $this->clearDefault($data, (int) $model->getKey());
            $provider = parent::update($model, $data);
            // Configuration/credential change — audited + timelined.
            $this->activity->record('integrations.config_updated', "Provider {$provider->getAttribute('name')} configuration updated", $provider, [
                'timeline' => true,
            ], (int) $provider->getAttribute('school_id'), 'integrations');

            return $provider;
        });
    }

    /**
     * Ensure a single default per (school, category).
     *
     * @param  array<string, mixed>  $data
     */
    private function clearDefault(array $data, ?int $exceptId = null): void
    {
        if (! empty($data['is_default']) && isset($data['school_id'], $data['category_id'])) {
            Provider::query()->where('school_id', $data['school_id'])->where('category_id', $data['category_id'])
                ->when($exceptId !== null, fn ($q) => $q->where('id', '!=', $exceptId))
                ->update(['is_default' => false]);
        }
    }
}
