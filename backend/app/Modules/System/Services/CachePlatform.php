<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized cache platform. Modules cache expensive reads (school settings,
 * master data, academic sessions, menus, roles/permissions, dashboard widgets, the
 * report catalog) through here and invalidate by GROUP — no stale data. Group
 * invalidation uses a version counter so it works on any cache driver (including
 * the file/array drivers that lack tag support).
 */
class CachePlatform
{
    /** Remember a value within a group for $ttl seconds. */
    public function remember(string $group, string $key, int $ttl, Closure $callback): mixed
    {
        return Cache::remember($this->key($group, $key), $ttl, $callback);
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return Cache::get($this->key($group, $key), $default);
    }

    public function put(string $group, string $key, mixed $value, int $ttl): void
    {
        Cache::put($this->key($group, $key), $value, $ttl);
    }

    public function forget(string $group, string $key): void
    {
        Cache::forget($this->key($group, $key));
    }

    /** Invalidate an entire group at once (bumps its version). */
    public function invalidate(string $group): void
    {
        Cache::increment($this->versionKey($group));
    }

    /** The known cacheable groups (documented for operators). @return array<int, string> */
    public function groups(): array
    {
        return ['settings', 'master_data', 'academic', 'menus', 'roles', 'permissions', 'dashboards', 'report_catalog'];
    }

    private function key(string $group, string $key): string
    {
        return "cache:{$group}:v{$this->version($group)}:{$key}";
    }

    private function version(string $group): int
    {
        $version = Cache::get($this->versionKey($group));
        if ($version === null) {
            Cache::forever($this->versionKey($group), 1);

            return 1;
        }

        return (int) $version;
    }

    private function versionKey(string $group): string
    {
        return "cachever:{$group}";
    }
}
