<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Http\Requests\BiometricEventRequest;
use App\Modules\Attendance\Http\Resources\BiometricLogResource;
use App\Modules\Attendance\Models\BiometricLog;
use App\Modules\Attendance\Services\BiometricIngestService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Real-time biometric ingestion endpoint for device connectors, plus read-only
 * access to the immutable biometric log.
 */
class BiometricController extends BaseController
{
    public function __construct(private readonly BiometricIngestService $service) {}

    /** Ingest a normalised event or a raw vendor payload (via its connector). */
    public function events(BiometricEventRequest $request): JsonResponse
    {
        $schoolId = $request->integer('school_id');
        $deviceIdentifier = $request->input('device_identifier');

        if ($request->filled('payload')) {
            $summary = $this->service->ingestRaw(
                $schoolId,
                (string) $request->input('vendor'),
                $deviceIdentifier,
                (array) $request->input('payload'),
            );

            return $this->ok($summary, 'Biometric payload processed.', 201);
        }

        $log = $this->service->ingestEvent(
            $schoolId,
            $deviceIdentifier,
            (string) $request->input('identity_number'),
            (string) $request->input('event_time'),
            $request->input('direction'),
            $request->only(['identity_number', 'event_time', 'direction', 'device_identifier']),
        );

        return $this->ok(new BiometricLogResource($log->load('device')), 'Biometric event processed.', 201);
    }

    /** The immutable biometric log (read-only). */
    public function logs(Request $request): JsonResponse
    {
        $logs = BiometricLog::query()
            ->with('device:id,name,device_identifier')
            ->when($request->filled('school_id'), fn ($q) => $q->where('school_id', $request->integer('school_id')))
            ->when($request->filled('processing_status'), fn ($q) => $q->where('processing_status', $request->string('processing_status')))
            ->when($request->filled('device_id'), fn ($q) => $q->where('device_id', $request->integer('device_id')))
            ->latest('id')
            ->paginate(min((int) $request->input('per_page', 25), 100));

        return $this->ok(BiometricLogResource::collection($logs), null, 200, [
            'total' => $logs->total(),
            'per_page' => $logs->perPage(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }
}
