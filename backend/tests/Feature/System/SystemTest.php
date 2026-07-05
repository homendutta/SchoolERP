<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\System\Enums\BackupType;
use App\Modules\System\Models\Backup;
use App\Modules\System\Services\BackupService;
use App\Modules\System\Services\CachePlatform;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Asylinx School', 'short_name' => 'AS', 'code' => 'AS', 'is_active' => true]);
    actingAsSuperAdmin();
});

// ---------------- Public liveness probe (no auth) ----------------
it('exposes a public health probe without auth', function (): void {
    app('auth')->forgetGuards();
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJsonStructure(['data' => ['status', 'score', 'time']]);
});

// ---------------- Health checks + overall score ----------------
it('reports component health and an overall score', function (): void {
    $data = $this->getJson('/api/v1/system/health')->assertOk()->json('data');

    expect($data['score'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
    $names = collect($data['components'])->pluck('name');
    foreach (['database', 'cache', 'queue', 'storage', 'scheduler', 'mail', 'integrations'] as $c) {
        expect($names)->toContain($c);
    }
    // Database + cache + storage are reachable in the test environment.
    expect(collect($data['components'])->firstWhere('name', 'database')['status'])->toBe('ok');
});

// ---------------- Diagnostics ----------------
it('returns system diagnostics', function (): void {
    $this->getJson('/api/v1/system/diagnostics')
        ->assertOk()
        ->assertJsonPath('data.php_version', PHP_VERSION)
        ->assertJsonStructure(['data' => ['laravel_version', 'database' => ['driver', 'version'], 'disk' => ['used_percent'], 'php_extensions']]);
});

// ---------------- Config validation ----------------
it('validates configuration and reports readiness', function (): void {
    $data = $this->getJson('/api/v1/system/config')->assertOk()->json('data');
    expect($data)->toHaveKey('ready');
    expect(collect($data['checks'])->firstWhere('check', 'app_key')['ok'])->toBeTrue();
    expect(collect($data['checks'])->firstWhere('check', 'database')['ok'])->toBeTrue();
});

// ---------------- Backup manifest + verification ----------------
it('records a backup manifest and verifies it', function (): void {
    $backup = $this->postJson('/api/v1/system/backups', ['type' => 'database', 'note' => 'nightly'])
        ->assertCreated()->json('data');

    expect($backup['status'])->toBe('completed');
    expect($backup['checksum'])->not->toBeNull();
    expect($backup['manifest']['database']['tables'])->toBeGreaterThan(0);
    $this->assertDatabaseHas('activity_logs', ['action' => 'system.backup_created']);

    $this->postJson("/api/v1/system/backups/{$backup['id']}/verify")
        ->assertOk()->assertJsonPath('data.status', 'verified');
});

it('creates a full backup manifest covering database, media and config', function (): void {
    $backup = app(BackupService::class)->create(BackupType::Full, $this->school->id, null);
    expect($backup->manifest)->toHaveKeys(['database', 'media', 'config']);
    expect(Backup::count())->toBe(1);
});

// ---------------- Failed-job monitoring (guarded) ----------------
it('reports failed job monitoring availability', function (): void {
    $this->getJson('/api/v1/system/failed-jobs')
        ->assertOk()
        ->assertJsonStructure(['data' => ['available', 'count', 'jobs']]);
});

// ---------------- Cache platform (grouped invalidation) ----------------
it('caches by group and invalidates the whole group', function (): void {
    $cache = app(CachePlatform::class);
    $cache->put('settings', 'site_name', 'Asylinx', 300);
    expect($cache->get('settings', 'site_name'))->toBe('Asylinx');

    $cache->invalidate('settings');
    // After invalidation the old value is gone (version bumped).
    expect($cache->get('settings', 'site_name'))->toBeNull();
});

// ---------------- Production dashboard ----------------
it('returns the production dashboard', function (): void {
    $this->getJson('/api/v1/system/dashboard')
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'health' => ['score', 'status', 'components'],
            'widgets' => ['overall_health', 'queue_pending', 'failed_jobs', 'scheduled_jobs', 'storage_used_percent', 'active_sessions', 'integration_providers', 'api_avg_ms'],
            'cache' => ['driver'],
            'queue' => ['driver'],
        ]]);
});

// ---------------- system:doctor command ----------------
it('runs the system doctor command', function (): void {
    $this->artisan('system:doctor')->assertExitCode(0);
});

// ---------------- Auth ----------------
it('requires authentication for the operator surface', function (): void {
    app('auth')->forgetGuards();
    $this->getJson('/api/v1/system/dashboard')->assertStatus(401);
});
