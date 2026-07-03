<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Models\Download;
use App\Modules\Cms\Models\Enquiry;
use App\Modules\Cms\Models\Event;
use App\Modules\Cms\Models\Gallery;
use App\Modules\Cms\Models\News;
use App\Modules\Cms\Models\Notice;
use App\Modules\Cms\Models\Page;
use Illuminate\Database\Eloquent\Builder;

class CmsDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $scope = fn (string $model): Builder => $model::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        return [
            'widgets' => [
                'pages' => (clone $scope(Page::class))->count(),
                'news' => (clone $scope(News::class))->count(),
                'events' => (clone $scope(Event::class))->count(),
                'notices' => (clone $scope(Notice::class))->count(),
                'gallery' => (clone $scope(Gallery::class))->count(),
                'downloads' => (clone $scope(Download::class))->count(),
                'enquiries' => (clone $scope(Enquiry::class))->count(),
                'draft_pages' => (clone $scope(Page::class))->where('status', 'draft')->count(),
            ],
            'charts' => [
                'publication_trend' => (clone $scope(News::class))->where('status', 'published')->get(['published_at'])
                    ->groupBy(fn ($n) => optional($n->published_at)->format('Y-m') ?? 'n/a')
                    ->map(fn ($g, $m) => ['label' => (string) $m, 'count' => $g->count()])
                    ->sortKeys()->values()->take(-12)->all(),
                'enquiry_trend' => (clone $scope(Enquiry::class))->get(['created_at'])
                    ->groupBy(fn ($e) => optional($e->created_at)->format('Y-m') ?? 'n/a')
                    ->map(fn ($g, $m) => ['label' => (string) $m, 'count' => $g->count()])
                    ->sortKeys()->values()->take(-12)->all(),
                'content_distribution' => [
                    ['label' => 'Pages', 'count' => (clone $scope(Page::class))->count()],
                    ['label' => 'News', 'count' => (clone $scope(News::class))->count()],
                    ['label' => 'Events', 'count' => (clone $scope(Event::class))->count()],
                    ['label' => 'Notices', 'count' => (clone $scope(Notice::class))->count()],
                    ['label' => 'Gallery', 'count' => (clone $scope(Gallery::class))->count()],
                    ['label' => 'Downloads', 'count' => (clone $scope(Download::class))->count()],
                ],
            ],
        ];
    }
}
