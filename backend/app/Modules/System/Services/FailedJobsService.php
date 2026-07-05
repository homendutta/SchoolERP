<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Failed-job monitoring + dead-letter handling. Reads Laravel's failed_jobs table
 * (when the database queue is configured) so operators never silently lose work.
 */
class FailedJobsService
{
    public function available(): bool
    {
        return Schema::hasTable('failed_jobs');
    }

    public function count(): int
    {
        return $this->available() ? (int) DB::table('failed_jobs')->count() : 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(int $limit = 100): array
    {
        if (! $this->available()) {
            return [];
        }

        return DB::table('failed_jobs')->latest('id')->limit($limit)->get()
            ->map(fn ($j) => [
                'id' => $j->id,
                'uuid' => $j->uuid ?? null,
                'connection' => $j->connection,
                'queue' => $j->queue,
                'failed_at' => $j->failed_at,
                'exception' => mb_substr((string) $j->exception, 0, 500),
            ])->all();
    }

    public function retry(string $id): bool
    {
        if (! $this->available()) {
            return false;
        }
        Artisan::call('queue:retry', ['id' => [$id]]);

        return true;
    }

    public function forget(string $id): bool
    {
        if (! $this->available()) {
            return false;
        }

        return DB::table('failed_jobs')->where('uuid', $id)->orWhere('id', $id)->delete() > 0;
    }
}
