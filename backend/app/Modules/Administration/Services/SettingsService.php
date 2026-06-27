<?php

declare(strict_types=1);

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\Setting;
use App\Platform\Shared\Services\BaseService;

/**
 * Dynamic settings store, grouped by category. No hardcoded settings pages —
 * any group/key can be read and written.
 */
class SettingsService extends BaseService
{
    /** @return array<string, array<string, mixed>> */
    public function all(?int $schoolId = null): array
    {
        return Setting::query()->where('school_id', $schoolId)->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->mapWithKeys(fn (Setting $s) => [$s->key => $s->typedValue()])->toArray())
            ->toArray();
    }

    /** @return array<string, mixed> */
    public function group(string $group, ?int $schoolId = null): array
    {
        return Setting::query()->where('school_id', $schoolId)->where('group', $group)->get()
            ->mapWithKeys(fn (Setting $s) => [$s->key => $s->typedValue()])
            ->toArray();
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function updateGroup(string $group, array $values, ?int $schoolId = null): array
    {
        $this->transaction(function () use ($group, $values, $schoolId): void {
            foreach ($values as $key => $value) {
                Setting::query()->updateOrCreate(
                    ['school_id' => $schoolId, 'group' => $group, 'key' => $key],
                    ['value' => $this->serialize($value), 'type' => $this->inferType($value)],
                );
            }
        });

        return $this->group($group, $schoolId);
    }

    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'bool',
            is_int($value) => 'int',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    private function serialize(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_array($value) => (string) json_encode($value),
            default => (string) $value,
        };
    }
}
