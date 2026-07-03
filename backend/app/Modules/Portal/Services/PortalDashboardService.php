<?php

declare(strict_types=1);

namespace App\Modules\Portal\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Portal\Enums\PortalRole;

/**
 * Role-aware dashboard aggregation. Reads every figure from the owning module via
 * PortalDataService — no calculations live here.
 */
class PortalDashboardService
{
    public function __construct(
        private readonly PortalContextService $context,
        private readonly PortalDataService $data,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $ctx = $this->context->resolve($user);
        $schoolId = (int) $user->school_id;

        return match ($ctx->role) {
            PortalRole::Parent, PortalRole::Student => $this->studentSide($ctx, $schoolId),
            PortalRole::Teacher => $this->teacherSide($ctx, $schoolId),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function studentSide(PortalContext $ctx, int $schoolId): array
    {
        $children = $ctx->students->map(function ($student): array {
            $attendance = $this->data->attendance((int) $student->id, 30);
            $fees = $this->data->fees((int) $student->id);

            return [
                'student_id' => (int) $student->id,
                'name' => $student->name ?? null,
                'attendance_percentage' => $attendance['summary']['percentage'],
                'today_present' => ($attendance['recent'][0]['status'] ?? null),
                'outstanding' => $fees['outstanding'] ?? ($fees['balance'] ?? 0),
            ];
        })->values()->all();

        return [
            'role' => $ctx->role->value,
            'widgets' => [
                'children' => count($children),
                'total_outstanding' => round((float) array_sum(array_map(fn ($c) => (float) $c['outstanding'], $children)), 2),
            ],
            'children' => $children,
            'notices' => $this->data->messages($schoolId)['circulars'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function teacherSide(PortalContext $ctx, int $schoolId): array
    {
        return [
            'role' => $ctx->role->value,
            'widgets' => [
                'staff_id' => $ctx->staff?->id,
                'name' => $ctx->staff?->name,
            ],
            'messages' => $this->data->messages($schoolId),
        ];
    }
}
