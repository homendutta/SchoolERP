<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Integrations\Jobs\DeliverWebhookJob;
use App\Modules\Integrations\Models\Category;
use App\Modules\Integrations\Models\Provider;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Services\EventBus;
use App\Modules\Integrations\Services\IntegrationService;
use App\Modules\Integrations\Services\RestConnector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Asylinx School', 'short_name' => 'AS', 'code' => 'AS', 'is_active' => true]);
    $this->payment = Category::create(['school_id' => $this->school->id, 'name' => 'Payment', 'code' => 'payment']);
    actingAsSuperAdmin();
});

function makeProvider(array $overrides = []): array
{
    return test()->postJson('/api/v1/integrations/providers', array_merge([
        'school_id' => test()->school->id,
        'category_id' => test()->payment->id,
        'name' => 'Manual Gateway',
        'code' => 'manual',
        'adapter' => 'manual',
        'status' => 'enabled',
        'config' => ['secret_key' => 'super_secret_123'],
        'is_default' => true,
    ], $overrides))->assertCreated()->json('data');
}

// ---------------- Registry / discovery ----------------
it('discovers registered adapters', function (): void {
    $codes = collect($this->getJson('/api/v1/integrations/adapters')->assertOk()->json('data'))->pluck('code');
    expect($codes)->toContain('manual')->toContain('rest');
});

// ---------------- Provider config is encrypted + never exposed ----------------
it('stores provider config encrypted and never returns it', function (): void {
    $provider = makeProvider();
    expect($provider)->not->toHaveKey('config');
    expect($provider['has_config'])->toBeTrue();

    // Raw DB value is ciphertext — the plaintext secret never appears.
    $raw = (string) DB::table('integration_providers')->where('id', $provider['id'])->value('config');
    expect($raw)->not->toContain('super_secret_123');
    // The model decrypts it back.
    expect(Provider::find($provider['id'])->config['secret_key'])->toBe('super_secret_123');
    $this->assertDatabaseHas('activity_logs', ['action' => 'integrations.provider_created']);
});

// ---------------- Provider selection (default → priority) ----------------
it('selects the default enabled provider for a category', function (): void {
    makeProvider(['name' => 'Secondary', 'priority' => 10, 'is_default' => false]);
    makeProvider(['name' => 'Primary', 'is_default' => true]);

    $selected = app(IntegrationService::class)->providerFor($this->school->id, 'payment');
    expect($selected?->name)->toBe('Primary');
});

// ---------------- Health check + test through the adapter ----------------
it('runs a provider health check and test through its adapter', function (): void {
    $provider = makeProvider();

    $this->getJson("/api/v1/integrations/providers/{$provider['id']}/health")
        ->assertOk()->assertJsonPath('data.status', 'healthy');
    expect(Provider::find($provider['id'])->health->value)->toBe('healthy');
    $this->assertDatabaseHas('activity_logs', ['action' => 'integrations.health_checked']);

    $this->postJson("/api/v1/integrations/providers/{$provider['id']}/test")
        ->assertOk()->assertJsonPath('data.ok', true);
});

// ---------------- Configuration change is audited (timeline) ----------------
it('audits configuration changes', function (): void {
    $provider = makeProvider();
    $this->putJson("/api/v1/integrations/providers/{$provider['id']}", ['config' => ['secret_key' => 'rotated']])->assertOk();
    $this->assertDatabaseHas('activity_logs', ['action' => 'integrations.config_updated']);
});

// ---------------- Event bus: immutable events fan out to webhooks ----------------
it('publishes an immutable event and queues outgoing webhook delivery', function (): void {
    Webhook::create([
        'school_id' => $this->school->id, 'name' => 'CRM', 'direction' => 'outgoing',
        'url' => 'https://crm.test/hook', 'secret' => 'shh', 'events' => ['student.created'],
    ]);

    Queue::fake();
    $event = app(EventBus::class)->publish($this->school->id, 'student.created', ['id' => 7], 'students');

    expect($event->event)->toBe('student.created');
    $this->assertDatabaseHas('integration_events', ['event' => 'student.created', 'source' => 'students']);
    $this->assertDatabaseHas('integration_webhook_deliveries', ['event' => 'student.created', 'status' => 'pending']);
    Queue::assertPushed(DeliverWebhookJob::class, 1);
});

// ---------------- Incoming webhook signature verification ----------------
it('verifies incoming webhook signatures', function (): void {
    $webhook = Webhook::create([
        'school_id' => $this->school->id, 'name' => 'Inbound', 'direction' => 'incoming', 'secret' => 'topsecret',
    ]);
    $body = json_encode(['ping' => true]);
    $good = hash_hmac('sha256', $body, 'topsecret');

    $this->call('POST', "/api/v1/public/integrations/webhooks/{$webhook->id}", [], [], [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE' => $good, 'HTTP_X_EVENT' => 'ping'], $body)
        ->assertOk();
    $this->assertDatabaseHas('integration_webhook_deliveries', ['webhook_id' => $webhook->id, 'status' => 'delivered']);

    $this->call('POST', "/api/v1/public/integrations/webhooks/{$webhook->id}", [], [], [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE' => 'wrong'], $body)
        ->assertStatus(401);
    $this->assertDatabaseHas('integration_webhook_deliveries', ['webhook_id' => $webhook->id, 'status' => 'failed']);
});

// ---------------- REST connector logs every request ----------------
it('logs successful and failed REST connector requests', function (): void {
    Http::fake([
        'https://api.test/ok' => Http::response(['ok' => true], 200),
        'https://api.test/bad' => Http::response('err', 500),
    ]);
    $connector = app(RestConnector::class);

    $ok = $connector->request('GET', 'https://api.test/ok', ['school_id' => $this->school->id, 'provider_code' => 'rest']);
    expect($ok['ok'])->toBeTrue();
    $bad = $connector->request('GET', 'https://api.test/bad', ['school_id' => $this->school->id, 'provider_code' => 'rest', 'retries' => 1]);
    expect($bad['ok'])->toBeFalse();

    $this->assertDatabaseHas('integration_logs', ['status' => 'success', 'response_code' => 200]);
    $this->assertDatabaseHas('integration_logs', ['status' => 'failure', 'response_code' => 500]);
});

// ---------------- Dashboard + auth ----------------
it('returns the monitoring dashboard', function (): void {
    makeProvider();
    $this->getJson("/api/v1/integrations/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['providers', 'enabled_providers', 'failed_requests', 'retry_queue', 'success_rate', 'avg_response_ms'],
            'charts' => ['provider_status', 'requests_by_provider', 'request_trend'],
        ]]);
});

it('requires authentication for admin integration endpoints', function (): void {
    app('auth')->forgetGuards();
    $this->getJson('/api/v1/integrations/dashboard')->assertStatus(401);
});
