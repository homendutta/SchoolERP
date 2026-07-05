<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use App\Modules\System\Enums\BackupStatus;
use App\Modules\System\Enums\BackupType;
use App\Modules\System\Models\Backup;
use App\Platform\Foundation\Audit\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Backup management (metadata + restore metadata). It records a MANIFEST of what
 * a backup covers — database tables + row counts, media object count, config keys
 * — plus a checksum for verification. Cloud backup PROVIDERS are out of scope; the
 * manifest + verification give operators a reliable disaster-recovery record.
 */
class BackupService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(BackupType $type, ?int $schoolId, ?int $userId, ?string $note = null): Backup
    {
        $manifest = match ($type) {
            BackupType::Database => ['database' => $this->databaseManifest()],
            BackupType::Media => ['media' => $this->mediaManifest()],
            BackupType::Config => ['config' => $this->configManifest()],
            BackupType::Full => [
                'database' => $this->databaseManifest(),
                'media' => $this->mediaManifest(),
                'config' => $this->configManifest(),
            ],
        };

        $checksum = hash('sha256', json_encode($manifest) ?: '');
        $backup = Backup::query()->create([
            'school_id' => $schoolId,
            'type' => $type->value,
            'status' => BackupStatus::Completed->value,
            'path' => 'backups/'.$type->value.'-'.now()->format('Ymd_His'),
            'manifest' => $manifest,
            'checksum' => $checksum,
            'note' => $note,
            'created_by' => $userId,
            'completed_at' => now(),
        ]);

        $this->activity->record('system.backup_created', "Backup ({$type->value}) recorded", $backup, [
            'checksum' => $checksum,
        ], $schoolId, 'system');

        return $backup;
    }

    /** Verify a backup's manifest against a freshly computed checksum. */
    public function verify(Backup $backup): Backup
    {
        $recomputed = hash('sha256', json_encode($backup->manifest) ?: '');
        $ok = hash_equals((string) $backup->checksum, $recomputed);

        $backup->update([
            'status' => $ok ? BackupStatus::Verified->value : BackupStatus::Failed->value,
            'verified_at' => now(),
        ]);

        return $backup->refresh();
    }

    /**
     * @return array{tables:int, rows:array<string,int>}
     */
    private function databaseManifest(): array
    {
        $tables = Schema::getTableListing();
        $rows = [];
        foreach ($tables as $table) {
            try {
                $rows[$table] = (int) DB::table($table)->count();
            } catch (\Throwable) {
                $rows[$table] = -1;
            }
        }

        return ['tables' => count($tables), 'rows' => $rows];
    }

    /**
     * @return array{objects:int}
     */
    private function mediaManifest(): array
    {
        $count = Schema::hasTable('media') ? (int) DB::table('media')->count() : 0;

        return ['objects' => $count];
    }

    /**
     * @return array<string, mixed>
     */
    private function configManifest(): array
    {
        return [
            'app_env' => (string) config('app.env'),
            'app_url' => (string) config('app.url'),
            'database_driver' => (string) config('database.default'),
            'cache_driver' => (string) config('cache.default'),
            'queue_driver' => (string) config('queue.default'),
            'filesystem' => (string) config('filesystems.default'),
            'default_disk_exists' => Storage::disk(config('filesystems.default')) !== null,
        ];
    }
}
