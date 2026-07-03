<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Models\Download;
use App\Modules\Cms\Models\Event;
use App\Modules\Cms\Models\Gallery;
use App\Modules\Cms\Models\Menu;
use App\Modules\Cms\Models\News;
use App\Modules\Cms\Models\Notice;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\Setting;
use App\Modules\Cms\Models\Video;
use App\Modules\Staff\Models\Staff;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Read-only projection of PUBLISHED CMS content for the public website. Returns
 * plain arrays (with Media ids resolved to URLs) so the static HTML/JS site can
 * consume clean JSON. Never exposes drafts or internal-only fields.
 */
class PublicContentService
{
    /** @var array<int, string|null> cache of resolved media urls */
    private array $urlCache = [];

    public function url(?int $mediaId): ?string
    {
        if ($mediaId === null) {
            return null;
        }
        if (! array_key_exists($mediaId, $this->urlCache)) {
            $this->urlCache[$mediaId] = Media::query()->find($mediaId)?->url();
        }

        return $this->urlCache[$mediaId];
    }

    /** Only published items (plus scheduled ones whose time has arrived). */
    private function live(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->where('status', 'published')
                ->orWhere(fn (Builder $s) => $s->where('status', 'scheduled')->where('scheduled_at', '<=', now()));
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(int $schoolId): array
    {
        $s = Setting::query()->firstOrCreate(['school_id' => $schoolId]);

        return [
            'site_name' => $s->site_name,
            'logo' => $this->url($s->logo_media_id),
            'favicon' => $this->url($s->favicon_media_id),
            'theme_colors' => $s->theme_colors,
            'email' => $s->email,
            'phone' => $s->phone,
            'address' => $s->address,
            'social_links' => $s->social_links,
            'footer' => $s->footer,
            'copyright' => $s->copyright,
            'google_map' => $s->google_map,
            'homepage_config' => $s->homepage_config,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function notices(int $schoolId, int $limit = 20): array
    {
        $today = Carbon::today()->toDateString();

        return $this->live(Notice::query()->where('school_id', $schoolId))
            ->where(fn (Builder $q) => $q->whereNull('publish_date')->orWhere('publish_date', '<=', $today))
            ->where(fn (Builder $q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today))
            ->orderByDesc('featured')->orderByDesc('publish_date')->limit($limit)->get()
            ->map(fn (Notice $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'priority' => $n->priority->value,
                'featured' => (bool) $n->featured,
                'publish_date' => $n->publish_date?->toDateString(),
                'attachment' => $this->url($n->attachment_media_id),
            ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function news(int $schoolId, int $limit = 20): array
    {
        return $this->live(News::query()->where('school_id', $schoolId))
            ->orderByDesc('featured')->orderByDesc('publish_date')->limit($limit)->get()
            ->map(fn (News $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'slug' => $n->slug,
                'excerpt' => $n->excerpt,
                'body' => $n->body,
                'featured' => (bool) $n->featured,
                'publish_date' => $n->publish_date?->toDateString(),
                'image' => $this->url($n->featured_image_media_id),
                'gallery' => collect($n->gallery ?? [])->map(fn ($id) => $this->url((int) $id))->filter()->values()->all(),
            ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function events(int $schoolId, int $limit = 20): array
    {
        return $this->live(Event::query()->where('school_id', $schoolId))
            ->orderBy('event_date')->limit($limit)->get()
            ->map(fn (Event $e) => [
                'id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'event_date' => $e->event_date?->toDateString(),
                'start_time' => $e->start_time,
                'end_time' => $e->end_time,
                'venue' => $e->venue,
                'registration_required' => (bool) $e->registration_required,
                'image' => $this->url($e->featured_image_media_id),
            ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function galleries(int $schoolId, int $limit = 50): array
    {
        return $this->live(Gallery::query()->where('school_id', $schoolId))->with('images')
            ->orderByDesc('featured')->latest('published_at')->limit($limit)->get()
            ->map(fn (Gallery $g) => [
                'id' => $g->id,
                'title' => $g->title,
                'description' => $g->description,
                'featured' => (bool) $g->featured,
                'cover' => $this->url($g->cover_media_id),
                'images' => $g->images->map(fn ($img) => [
                    'url' => $this->url((int) $img->media_id),
                    'caption' => $img->caption,
                ])->all(),
            ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function videos(int $schoolId, int $limit = 50): array
    {
        return $this->live(Video::query()->where('school_id', $schoolId))
            ->orderByDesc('featured')->latest('published_at')->limit($limit)->get()
            ->map(fn (Video $v) => [
                'id' => $v->id,
                'title' => $v->title,
                'description' => $v->description,
                'provider' => $v->provider->value,
                'video_url' => $v->video_url,
                'file' => $this->url($v->media_id),
                'thumbnail' => $this->url($v->thumbnail_media_id),
            ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function downloads(int $schoolId, int $limit = 100): array
    {
        return $this->live(Download::query()->where('school_id', $schoolId))->with('category:id,name')
            ->orderBy('title')->limit($limit)->get()
            ->map(fn (Download $d) => [
                'id' => $d->id,
                'title' => $d->title,
                'description' => $d->description,
                'category' => $d->category?->name,
                'file' => $this->url($d->media_id),
            ])->all();
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function menus(int $schoolId): array
    {
        return Menu::query()->where('school_id', $schoolId)->where('status', 'active')
            ->orderBy('sequence')->get()
            ->groupBy(fn (Menu $m) => $m->location->value)
            ->map(fn ($items) => $items->map(fn (Menu $m) => [
                'label' => $m->label, 'url' => $m->url, 'parent_id' => $m->parent_id, 'sequence' => $m->sequence,
            ])->all())
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function page(int $schoolId, string $slug): ?array
    {
        $page = $this->live(Page::query()->where('school_id', $schoolId)->where('slug', $slug))->first();
        if ($page === null) {
            return null;
        }

        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'body' => $page->body,
            'seo' => $page->seo,
        ];
    }

    /**
     * Public staff directory — reuses the Staff module (never duplicated).
     *
     * @return array<int, array<string, mixed>>
     */
    public function staffDirectory(int $schoolId, int $limit = 200): array
    {
        return Staff::query()->where('school_id', $schoolId)->where('status', 'active')
            ->with(['department:id,label,value', 'designation:id,label,value'])
            ->orderBy('name')->limit($limit)->get()
            ->map(fn (Staff $s) => [
                'name' => $s->name,
                'employee_number' => $s->employee_number,
                'department' => $s->department?->label,
                'designation' => $s->designation?->label,
                'photo' => $this->url($s->photo_media_id),
            ])->all();
    }

    /**
     * The homepage payload — settings + all dynamic sections in one call.
     *
     * @return array<string, mixed>
     */
    public function homepage(int $schoolId): array
    {
        return [
            'settings' => $this->settings($schoolId),
            'menus' => $this->menus($schoolId),
            'notices' => $this->notices($schoolId, 8),
            'news' => $this->news($schoolId, 6),
            'events' => $this->events($schoolId, 6),
            'galleries' => $this->galleries($schoolId, 6),
            'videos' => $this->videos($schoolId, 6),
            'downloads' => $this->downloads($schoolId, 10),
        ];
    }
}
