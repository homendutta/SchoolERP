<?php

declare(strict_types=1);

use App\Modules\Administration\Models\Permission;
use App\Modules\Administration\Models\Role;
use App\Modules\Administration\Models\School;
use App\Modules\Administration\Models\User;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    Storage::fake('local');
    Storage::fake('public');
    actingAsSuperAdmin();
});

// ---------------- Upload ----------------
it('uploads an image and returns a media id with dimensions and checksum', function (): void {
    $res = $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('photo.jpg', 800, 600),
        'collection' => 'students',
    ])->assertCreated();

    $id = $res->json('data.id');
    expect($id)->not->toBeNull();
    $res->assertJsonPath('data.collection', 'students')
        ->assertJsonPath('data.width', 800)
        ->assertJsonPath('data.height', 600)
        ->assertJsonPath('data.extension', 'jpg');
    expect($res->json('data.checksum'))->not->toBeNull();
    expect($res->json('data.uuid'))->not->toBeNull();

    $media = Media::find($id);
    Storage::disk($media->disk)->assertExists($media->path);
});

it('uploads a pdf document', function (): void {
    $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->create('report.pdf', 200, 'application/pdf'),
    ])->assertCreated()->assertJsonPath('data.extension', 'pdf');
});

it('stores public media on the public disk and private on the local disk', function (): void {
    $pub = $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('logo.png', 100, 100),
        'visibility' => 'public',
    ])->assertCreated();
    expect($pub->json('data.disk'))->toBe('public');

    $priv = $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('id.png', 100, 100),
    ])->assertCreated();
    expect($priv->json('data.disk'))->toBe('local');
    expect($priv->json('data.visibility'))->toBe('private');
});

// ---------------- Invalid type ----------------
it('rejects an executable/script upload', function (): void {
    $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->create('evil.php', 5, 'text/x-php'),
    ])->assertStatus(422)->assertJsonPath('code', 'MEDIA_BLOCKED_TYPE');

    expect(Media::count())->toBe(0);
});

it('rejects an unsupported file type', function (): void {
    $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->create('data.csv', 5, 'text/csv'),
    ])->assertStatus(422)->assertJsonPath('code', 'MEDIA_UNSUPPORTED_TYPE');
});

// ---------------- Oversized ----------------
it('rejects a file that exceeds the category size limit', function (): void {
    // Document limit is 10 MB; 11 MB is under the 20 MB global request cap so the
    // service-level category check is what rejects it.
    $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->create('huge.pdf', 11000, 'application/pdf'),
    ])->assertStatus(422)->assertJsonPath('code', 'MEDIA_TOO_LARGE');
});

// ---------------- Delete ----------------
it('deletes a media record and its stored file', function (): void {
    $id = $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('p.jpg', 200, 200),
    ])->json('data.id');
    $media = Media::find($id);
    Storage::disk($media->disk)->assertExists($media->path);

    $this->deleteJson("/api/v1/media/{$id}")->assertOk();

    expect(Media::find($id))->toBeNull();
    Storage::disk($media->disk)->assertMissing($media->path);
});

// ---------------- Replace ----------------
it('replaces the file behind a media record, keeping the same id', function (): void {
    $id = $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('first.jpg', 300, 300),
    ])->json('data.id');
    $before = Media::find($id);

    $this->postJson("/api/v1/media/{$id}/replace", [
        'file' => UploadedFile::fake()->image('second.png', 120, 90),
    ])->assertOk()
        ->assertJsonPath('data.id', $id)
        ->assertJsonPath('data.extension', 'png')
        ->assertJsonPath('data.width', 120);

    $after = Media::find($id);
    expect($after->checksum)->not->toBe($before->checksum);
    Storage::disk($after->disk)->assertExists($after->path);
    Storage::disk($before->disk)->assertMissing($before->path);
});

// ---------------- Show / download ----------------
it('returns media metadata and streams the file', function (): void {
    $id = $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('p.jpg', 100, 100),
    ])->json('data.id');

    $this->getJson("/api/v1/media/{$id}")->assertOk()->assertJsonPath('data.id', $id);
    $this->get("/api/v1/media/{$id}/download")->assertOk();
});

// ---------------- Permission validation ----------------
it('forbids uploads without the media.upload permission', function (): void {
    // A plain authenticated user with a role that lacks media permissions.
    Permission::query()->firstOrCreate(['slug' => 'media.upload'], ['name' => 'Upload media', 'module' => 'Media', 'action' => 'upload']);
    $role = Role::create(['slug' => 'viewer', 'name' => 'Viewer', 'is_system' => false]);
    $user = User::create([
        'name' => 'No Perms', 'email' => 'noperms@asylinx.test', 'username' => 'noperms',
        'password' => 'Password@123', 'status' => 'active',
    ]);
    $user->assignRole($role);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('p.jpg', 100, 100),
    ])->assertStatus(403);
});

// ---------------- Usage check before delete ----------------
it('refuses to delete media that is still referenced by business data', function (): void {
    $school = School::create(['name' => 'S', 'short_name' => 'S', 'code' => 'S', 'is_active' => true]);
    $id = $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('photo.jpg', 200, 200),
    ])->json('data.id');

    Student::create([
        'school_id' => $school->id, 'admission_number' => '1', 'name' => 'Asha',
        'status' => 'active', 'photo_media_id' => $id,
    ]);

    $this->deleteJson("/api/v1/media/{$id}")
        ->assertStatus(422)->assertJsonPath('code', 'MEDIA_IN_USE');

    expect(Media::find($id))->not->toBeNull(); // still present, no orphaned reference
});

// ---------------- Thumbnails into a dedicated location ----------------
it('generates a thumbnail derivative in the dedicated derivatives directory', function (): void {
    $id = $this->postJson('/api/v1/media/upload', [
        'file' => UploadedFile::fake()->image('photo.jpg', 800, 600),
    ])->assertCreated()->json('data.id');

    $media = Media::find($id);
    $thumb = $media->metadata['thumbnails']['thumb'] ?? null;

    expect($thumb)->not->toBeNull();
    expect($thumb)->toStartWith('derivatives/');
    // Original is untouched and the derivative lives at its own path.
    expect($thumb)->not->toBe($media->path);
    Storage::disk($media->disk)->assertExists($media->path);
    Storage::disk($media->disk)->assertExists($thumb);
});
