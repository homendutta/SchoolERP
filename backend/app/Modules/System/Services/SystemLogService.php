<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use App\Platform\Foundation\Audit\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Centralized, filterable log read. Unifies the Audit Engine's activity log (error/
 * security/queue/integration/performance categories are recorded there via
 * log_name) behind a single filtered endpoint for operators.
 */
class SystemLogService
{
    /**
     * @param  array<string, mixed>  $params
     * @return LengthAwarePaginator<int, ActivityLog>
     */
    public function list(array $params): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->when($params['school_id'] ?? null, fn ($q, $v) => $q->where('school_id', $v))
            ->when($params['log_name'] ?? null, fn ($q, $v) => $q->where('log_name', $v))
            ->when($params['action'] ?? null, fn ($q, $v) => $q->where('action', 'like', "%{$v}%"))
            ->when($params['from'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($params['to'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', $v))
            ->latest('id')
            ->paginate((int) ($params['per_page'] ?? 50));
    }
}
