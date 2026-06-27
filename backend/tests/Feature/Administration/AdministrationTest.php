<?php

declare(strict_types=1);

use App\Modules\Administration\Models\MasterDataType;
use App\Modules\Administration\Models\NumberSequence;
use Illuminate\Http\UploadedFile;

beforeEach(function (): void {
    actingAsSuperAdmin();
});

// ---------------- School Settings ----------------
it('returns the school settings with focused sections', function (): void {
    $this->getJson('/api/v1/admin/school')
        ->assertOk()
        ->assertJsonStructure(['data' => ['general', 'branding', 'contact', 'regional', 'academic']]);
});

it('updates school settings across sections', function (): void {
    $this->putJson('/api/v1/admin/school', [
        'general' => ['name' => 'New Name'],
        'contact' => ['email' => 'hello@school.test'],
        'regional' => ['currency' => 'USD'],
    ])->assertOk()->assertJsonPath('data.general.name', 'New Name');

    $this->assertDatabaseHas('school_contact', ['email' => 'hello@school.test']);
    $this->assertDatabaseHas('school_regional', ['currency' => 'USD']);
});

// ---------------- Master Data Engine ----------------
it('performs CRUD + archive + restore on master data values', function (): void {
    $type = MasterDataType::create(['slug' => 'departments', 'name' => 'Departments']);

    $id = $this->postJson('/api/v1/admin/master-data/values', [
        'type_id' => $type->id, 'label' => 'Science', 'value' => 'science',
    ])->assertCreated()->json('data.id');

    $this->putJson("/api/v1/admin/master-data/values/{$id}", ['label' => 'Sciences'])
        ->assertOk()->assertJsonPath('data.label', 'Sciences');

    $this->postJson("/api/v1/admin/master-data/values/{$id}/archive")->assertOk();
    $this->assertSoftDeleted('master_data_values', ['id' => $id]);

    $this->postJson("/api/v1/admin/master-data/values/{$id}/restore")->assertOk();
    $this->assertDatabaseHas('master_data_values', ['id' => $id, 'deleted_at' => null]);
});

it('searches master data values by text and relation', function (): void {
    $hr = MasterDataType::create(['slug' => 'designations', 'name' => 'Designations']);
    $hr->values()->createMany([
        ['label' => 'Principal', 'value' => 'principal'],
        ['label' => 'Teacher', 'value' => 'teacher'],
    ]);

    $this->getJson('/api/v1/admin/master-data/values?'.http_build_query(['search' => ['label' => 'princ']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson('/api/v1/admin/master-data/values?'.http_build_query(['search' => ['type' => 'designations']]))
        ->assertOk()->assertJsonCount(2, 'data');
});

it('bulk deletes master data values', function (): void {
    $type = MasterDataType::create(['slug' => 'cats', 'name' => 'Cats']);
    $ids = collect(['a', 'b', 'c'])->map(fn ($v) => $type->values()->create(['label' => $v, 'value' => $v])->id)->all();

    $this->postJson('/api/v1/admin/master-data/values/bulk-delete', ['ids' => $ids])
        ->assertOk()->assertJsonPath('data.deleted', 3);
});

// ---------------- Settings Engine ----------------
it('reads and writes grouped settings', function (): void {
    $this->putJson('/api/v1/admin/settings/appearance', ['values' => ['theme_color' => '#123456']])
        ->assertOk()->assertJsonPath('data.theme_color', '#123456');

    $this->getJson('/api/v1/admin/settings/appearance')->assertOk()->assertJsonPath('data.theme_color', '#123456');
});

it('rejects an unknown settings group', function (): void {
    $this->putJson('/api/v1/admin/settings/nonsense', ['values' => ['x' => 1]])->assertStatus(422);
});

// ---------------- Number Generator ----------------
it('previews, updates and resets a number sequence', function (): void {
    $seq = NumberSequence::create(['key' => 'staff_number', 'initial_number' => 1, 'padding' => 4, 'format' => '{number}']);

    $this->getJson('/api/v1/admin/number-sequences/staff_number/preview')
        ->assertOk()->assertJsonPath('data.next', '0001');

    $this->putJson("/api/v1/admin/number-sequences/{$seq->id}", ['prefix' => 'STF-'])
        ->assertOk()->assertJsonPath('data.prefix', 'STF-');

    $this->postJson('/api/v1/admin/number-sequences/staff_number/reset')->assertOk();
});

// ---------------- Feature Flags ----------------
it('lists and toggles feature flags', function (): void {
    $this->getJson('/api/v1/admin/feature-flags')->assertOk()
        ->assertJsonFragment(['key' => 'library']);

    $this->putJson('/api/v1/admin/feature-flags/library', ['is_enabled' => true])
        ->assertOk()->assertJsonPath('data.is_enabled', true);

    $this->assertDatabaseHas('feature_flags', ['key' => 'library', 'is_enabled' => true]);
});

// ---------------- Gateways ----------------
it('saves and tests the email gateway', function (): void {
    $this->putJson('/api/v1/admin/gateways/email', [
        'host' => 'smtp.test', 'port' => 587, 'from_address' => 'no-reply@school.test',
    ])->assertOk();

    $this->postJson('/api/v1/admin/gateways/email/test')->assertOk()->assertJsonPath('data.ok', true);
});

it('lists payment providers and updates one', function (): void {
    $this->getJson('/api/v1/admin/gateways/payments')->assertOk()->assertJsonFragment(['provider' => 'razorpay']);

    $this->putJson('/api/v1/admin/gateways/payments/razorpay', [
        'key_id' => 'k', 'key_secret' => 's', 'mode' => 'test', 'is_enabled' => true,
    ])->assertOk()->assertJsonPath('data.is_enabled', true);

    $this->putJson('/api/v1/admin/gateways/payments/unknown', ['key_id' => 'x'])->assertNotFound();
});

// ---------------- Import / Export ----------------
it('exports rows as csv', function (): void {
    $res = $this->postJson('/api/v1/admin/export', [
        'format' => 'csv', 'filename' => 'demo',
        'headings' => ['A', 'B'], 'rows' => [[1, 2], [3, 4]],
    ]);
    $res->assertOk();
    expect($res->headers->get('content-type'))->toContain('text/csv');
});

it('reports unavailable export formats clearly', function (): void {
    $this->postJson('/api/v1/admin/export', [
        'format' => 'pdf', 'headings' => ['A'], 'rows' => [[1]],
    ])->assertStatus(422)->assertJsonPath('code', 'EXPORT_FORMAT_UNAVAILABLE');
});

it('parses an uploaded csv into rows', function (): void {
    $csv = "name,code\nAlpha,A1\nBeta,B2\n";
    $file = UploadedFile::fake()->createWithContent('data.csv', $csv);

    $this->post('/api/v1/admin/import/upload', ['file' => $file], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('data.count', 2)
        ->assertJsonPath('data.headings', ['name', 'code']);
});

it('fails import execute when no importer is registered', function (): void {
    $this->postJson('/api/v1/admin/import/execute', ['key' => 'students', 'rows' => [['x' => 1]]])
        ->assertStatus(422)->assertJsonPath('code', 'IMPORTER_NOT_FOUND');
});
