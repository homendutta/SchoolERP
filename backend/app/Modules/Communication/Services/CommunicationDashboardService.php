<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\Models\CommunicationMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CommunicationDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $base = fn (): Builder => CommunicationMessage::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        $sent = (clone $base())->whereIn('status', ['sent', 'delivered', 'read'])->count();
        $failed = (clone $base())->where('status', 'failed')->count();
        $pending = (clone $base())->whereIn('status', ['pending', 'processing'])->count();
        $scheduled = (clone $base())->whereNotNull('scheduled_at')->where('scheduled_at', '>', now())->where('status', 'pending')->count();
        $deliverable = (clone $base())->whereNotIn('status', ['cancelled'])->count();

        return [
            'widgets' => [
                'messages_sent' => $sent,
                'failed' => $failed,
                'pending' => $pending,
                'scheduled' => $scheduled,
                'delivery_rate' => $deliverable > 0 ? round($sent / $deliverable * 100, 1) : 0.0,
            ],
            'charts' => [
                'daily_messages' => $this->series(clone $base()),
                'channel_usage' => $this->byChannel(clone $base()),
                'delivery_success' => [
                    ['label' => 'Delivered', 'count' => $sent],
                    ['label' => 'Failed', 'count' => $failed],
                ],
                'failure_trend' => $this->series((clone $base())->where('status', 'failed')),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function series(Builder $query): array
    {
        return $query->get(['created_at'])
            ->groupBy(fn ($m) => Carbon::parse($m->created_at)->format('Y-m-d'))
            ->map(fn ($g, $period) => ['label' => $period, 'count' => $g->count()])
            ->sortKeys()->values()->take(-14)->all();
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function byChannel(Builder $query): array
    {
        return $query->get(['channel'])
            ->groupBy(fn ($m) => $m->channel->value)
            ->map(fn ($g, $channel) => ['label' => $channel, 'count' => $g->count()])
            ->values()->all();
    }
}
