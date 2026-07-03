<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Cms\Models\Category;
use App\Modules\Cms\Models\Notice;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    actingAsSuperAdmin();
});

function makeMedia(): Media
{
    return Media::create([
        'school_id' => test()->school->id, 'collection' => 'cms', 'disk' => 'public', 'visibility' => 'public',
        'path' => 'cms/x.jpg', 'filename' => 'x.jpg', 'original_filename' => 'x.jpg', 'mime_type' => 'image/jpeg', 'size' => 10,
    ]);
}

// ---------------- Settings (per-school singleton) ----------------
it('manages website settings as a singleton and exposes them publicly', function (): void {
    $this->putJson('/api/v1/cms/settings', [
        'school_id' => $this->school->id, 'site_name' => 'AJA', 'email' => 'mail@aja.in', 'phone' => '123',
        'social_links' => ['facebook' => 'https://fb.com/aja'],
    ])->assertOk()->assertJsonPath('data.site_name', 'AJA');

    // Updating again must not create a second row.
    $this->putJson('/api/v1/cms/settings', ['school_id' => $this->school->id, 'site_name' => 'AJA 2'])->assertOk();
    $this->assertDatabaseCount('cms_settings', 1);

    $this->getJson("/api/v1/cms/public/settings?school_id={$this->school->id}")
        ->assertOk()->assertJsonPath('data.site_name', 'AJA 2')->assertJsonPath('data.email', 'mail@aja.in');
});

// ---------------- Notices (published + in-window only on the public site) ----------------
it('publishes notices and shows only current ones publicly', function (): void {
    $category = Category::create(['school_id' => $this->school->id, 'type' => 'notice', 'name' => 'General']);

    // Draft notice — not public.
    $this->postJson('/api/v1/cms/notices', [
        'school_id' => $this->school->id, 'category_id' => $category->id, 'title' => 'Draft notice', 'status' => 'draft',
    ])->assertCreated();

    // Published, in-window — public + audited + communication.
    $this->postJson('/api/v1/cms/notices', [
        'school_id' => $this->school->id, 'title' => 'Holiday tomorrow', 'status' => 'published', 'featured' => true,
        'publish_date' => now()->subDay()->toDateString(), 'expiry_date' => now()->addWeek()->toDateString(),
    ])->assertCreated();
    $this->assertDatabaseHas('activity_logs', ['action' => 'cms.notice_published']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'cms.notice_published']);

    // Published but expired — hidden.
    Notice::create([
        'school_id' => $this->school->id, 'title' => 'Old notice', 'status' => 'published',
        'publish_date' => now()->subMonth()->toDateString(), 'expiry_date' => now()->subWeek()->toDateString(),
        'published_at' => now()->subMonth(),
    ]);

    $this->getJson("/api/v1/cms/public/notices?school_id={$this->school->id}")
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Holiday tomorrow');
});

// ---------------- News: draft hidden, publish reveals ----------------
it('keeps news drafts private until published', function (): void {
    $news = $this->postJson('/api/v1/cms/news', [
        'school_id' => $this->school->id, 'title' => 'Sports Day', 'slug' => 'sports-day', 'excerpt' => 'Fun', 'status' => 'draft',
    ])->assertCreated()->json('data');

    $this->getJson("/api/v1/cms/public/news?school_id={$this->school->id}")->assertOk()->assertJsonCount(0, 'data');

    $this->putJson("/api/v1/cms/news/{$news['id']}", ['status' => 'published'])->assertOk();
    $this->assertDatabaseHas('activity_logs', ['action' => 'cms.news_published']);
    $this->getJson("/api/v1/cms/public/news?school_id={$this->school->id}")
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Sports Day');
});

// ---------------- Events ----------------
it('publishes an event and notifies through Communication', function (): void {
    $this->postJson('/api/v1/cms/events', [
        'school_id' => $this->school->id, 'title' => 'Annual Function', 'event_date' => now()->addMonth()->toDateString(),
        'venue' => 'Auditorium', 'status' => 'published',
    ])->assertCreated();

    $this->assertDatabaseHas('communication_batches', ['event' => 'cms.event_published']);
    $this->getJson("/api/v1/cms/public/events?school_id={$this->school->id}")
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.venue', 'Auditorium');
});

// ---------------- Gallery with Media images ----------------
it('creates a photo album with media images and serves it publicly', function (): void {
    $media = makeMedia();
    $gallery = $this->postJson('/api/v1/cms/gallery', [
        'school_id' => $this->school->id, 'title' => 'Campus', 'status' => 'published',
        'images' => [['media_id' => $media->id, 'caption' => 'Front gate', 'sequence' => 0]],
    ])->assertCreated()->json('data');

    expect($gallery['images'])->toHaveCount(1);
    $this->getJson("/api/v1/cms/public/gallery?school_id={$this->school->id}")
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.images.0.caption', 'Front gate');
});

// ---------------- Videos + downloads ----------------
it('serves published videos and downloads publicly', function (): void {
    $this->postJson('/api/v1/cms/videos', [
        'school_id' => $this->school->id, 'title' => 'Tour', 'provider' => 'youtube',
        'video_url' => 'https://youtu.be/abc', 'status' => 'published',
    ])->assertCreated();
    $media = makeMedia();
    $this->postJson('/api/v1/cms/downloads', [
        'school_id' => $this->school->id, 'title' => 'Prospectus', 'media_id' => $media->id, 'status' => 'published',
    ])->assertCreated();

    $this->getJson("/api/v1/cms/public/videos?school_id={$this->school->id}")
        ->assertOk()->assertJsonPath('data.0.video_url', 'https://youtu.be/abc');
    $this->getJson("/api/v1/cms/public/downloads?school_id={$this->school->id}")
        ->assertOk()->assertJsonPath('data.0.title', 'Prospectus');
});

// ---------------- Menus (CMS-managed, grouped by location) ----------------
it('manages menus and returns them grouped by location', function (): void {
    $this->postJson('/api/v1/cms/menus', ['school_id' => $this->school->id, 'location' => 'header', 'label' => 'Home', 'url' => '/', 'sequence' => 0])->assertCreated();
    $this->postJson('/api/v1/cms/menus', ['school_id' => $this->school->id, 'location' => 'footer', 'label' => 'Contact', 'url' => '/contact', 'sequence' => 0])->assertCreated();

    $this->getJson("/api/v1/cms/public/menus?school_id={$this->school->id}")
        ->assertOk()->assertJsonPath('data.header.0.label', 'Home')->assertJsonPath('data.footer.0.label', 'Contact');
});

// ---------------- Pages (SEO) served by slug ----------------
it('publishes a page and serves it by slug with SEO', function (): void {
    $this->postJson('/api/v1/cms/pages', [
        'school_id' => $this->school->id, 'title' => 'About Us', 'slug' => 'about', 'body' => '<p>Hello</p>',
        'seo' => ['meta_title' => 'About | AJA'], 'status' => 'published',
    ])->assertCreated();

    $this->getJson("/api/v1/cms/public/pages/about?school_id={$this->school->id}")
        ->assertOk()->assertJsonPath('data.title', 'About Us')->assertJsonPath('data.seo.meta_title', 'About | AJA');
    $this->getJson("/api/v1/cms/public/pages/missing?school_id={$this->school->id}")->assertStatus(404);
});

// ---------------- Public contact form + admission enquiry ----------------
it('captures a public contact submission and notifies staff', function (): void {
    $this->postJson('/api/v1/cms/public/forms', [
        'school_id' => $this->school->id, 'type' => 'contact', 'name' => 'Sita', 'email' => 's@x.com', 'message' => 'Hi',
    ])->assertCreated();

    $this->assertDatabaseHas('cms_form_submissions', ['name' => 'Sita', 'type' => 'contact']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'cms.contact_submitted']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'cms.contact_submitted']);
});

it('captures an admission enquiry without creating an admission', function (): void {
    $this->postJson('/api/v1/cms/public/enquiries', [
        'school_id' => $this->school->id, 'parent_name' => 'Ram', 'student_name' => 'Kid', 'interested_class' => 'V', 'phone' => '999',
    ])->assertCreated();

    $this->assertDatabaseHas('cms_enquiries', ['parent_name' => 'Ram', 'status' => 'new']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'cms.enquiry_submitted']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'cms.enquiry_submitted']);
    // Enquiry only — no admission record is created.
    expect(Schema::hasTable('admissions')
        ? DB::table('admissions')->count() : 0)->toBe(0);
});

// ---------------- Search + dashboard ----------------
it('searches news and returns the CMS dashboard', function (): void {
    $this->postJson('/api/v1/cms/news', ['school_id' => $this->school->id, 'title' => 'Merit Scholarship', 'slug' => 'merit', 'status' => 'published'])->assertCreated();

    $this->getJson('/api/v1/cms/news?'.http_build_query(['search' => ['title' => 'Merit']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/cms/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['pages', 'news', 'events', 'notices', 'gallery', 'downloads', 'enquiries', 'draft_pages'],
            'charts' => ['publication_trend', 'enquiry_trend', 'content_distribution'],
        ]]);
});
