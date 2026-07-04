<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;
use App\Modules\Reports\Enums\ScheduleFrequency;
use App\Modules\Reports\Models\ReportSchedule;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Scheduled reports. Runs are QUEUED; delivery emails go through the Communication
 * Engine (never sent directly). Report execution/export reuses the central engine.
 */
class ScheduleService extends BaseCrudService
{
    public function __construct(
        private readonly RunReportService $runner,
        private readonly CommunicationEngine $communication,
    ) {}

    protected function model(): string
    {
        return ReportSchedule::class;
    }

    protected function searchable(): array
    {
        return ['name', 'report_key'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'report_key', 'frequency', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'next_run_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        $frequency = ScheduleFrequency::from($data['frequency']);
        $data['next_run_at'] = $frequency->next(Carbon::now());

        return parent::create($data);
    }

    /** Run a schedule now (queues the export + publishes the delivery notification). */
    public function runNow(User $user, ReportSchedule $schedule): ReportSchedule
    {
        $export = $this->runner->queueExport($user, $schedule->report_key, array_merge(
            ['school_id' => $schedule->school_id], $schedule->filters ?? []
        ), $schedule->format->value);

        $schedule->update([
            'last_run_at' => Carbon::now(),
            'next_run_at' => $schedule->frequency->next(Carbon::now()),
        ]);

        if (! empty($schedule->recipients)) {
            $this->communication->publish(new CommunicationRequestData(
                schoolId: (int) $schedule->school_id,
                channel: CommunicationChannel::Email,
                audienceType: AudienceType::Custom,
                subject: "Scheduled report: {$schedule->name}",
                body: "Your scheduled report '{$schedule->name}' has been queued (export #{$export->id}).",
                source: 'reports',
                event: 'reports.scheduled_delivered',
            ));
        }

        return $schedule->refresh();
    }
}
