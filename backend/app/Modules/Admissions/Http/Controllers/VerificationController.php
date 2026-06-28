<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Controllers;

use App\Modules\Admissions\Enums\VerificationStatus;
use App\Modules\Admissions\Http\Requests\VerificationRequest;
use App\Modules\Admissions\Http\Resources\ApplicationResource;
use App\Modules\Admissions\Http\Resources\DocumentResource;
use App\Modules\Admissions\Http\Resources\VerificationLogResource;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Models\AdmissionDocument;
use App\Modules\Admissions\Services\VerificationService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class VerificationController extends BaseController
{
    public function __construct(private readonly VerificationService $service) {}

    /** Verify (or hold/reject) an entire application. */
    public function application(VerificationRequest $request, int|string $id): JsonResponse
    {
        $application = AdmissionApplication::query()->findOrFail($id);
        $status = VerificationStatus::from((string) $request->validated('status'));

        $updated = $this->service->verifyApplication($application, $status, $request->validated('remarks'));

        return $this->ok(new ApplicationResource($updated), 'Application verification updated.');
    }

    /** Verify (or hold/reject) a single document. */
    public function document(VerificationRequest $request, int|string $id): JsonResponse
    {
        $document = AdmissionDocument::query()->findOrFail($id);
        $status = VerificationStatus::from((string) $request->validated('status'));

        $updated = $this->service->verifyDocument($document, $status, $request->validated('remarks'));

        return $this->ok(new DocumentResource($updated), 'Document verification updated.');
    }

    /** Full verification history for an application. */
    public function history(int|string $id): JsonResponse
    {
        return $this->ok(VerificationLogResource::collection($this->service->history((int) $id)));
    }
}
