<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Models\GeneratedDocument;
use App\Modules\Documents\Models\Template;
use App\Modules\Documents\Models\Verification;
use Illuminate\Database\Eloquent\Builder;

class DocumentDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $docs = fn (): Builder => GeneratedDocument::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $verifs = fn (): Builder => Verification::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        return [
            'widgets' => [
                'documents_generated' => (clone $docs())->count(),
                'certificates_issued' => (clone $docs())->where('status', 'issued')->count(),
                'revoked' => (clone $docs())->where('status', 'revoked')->count(),
                'verified_documents' => (clone $verifs())->where('result', 'valid')->distinct('document_id')->count('document_id'),
                'rejected_requests' => (clone $verifs())->where('result', 'invalid')->count(),
                'templates' => Template::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count(),
            ],
            'charts' => [
                'documents_by_category' => (clone $docs())->with('certificateType.category:id,name')->get()
                    ->groupBy(fn ($d) => $d->certificateType?->category?->name ?? 'Uncategorised')
                    ->map(fn ($g, $name) => ['label' => (string) $name, 'count' => $g->count()])->values()->all(),
                'monthly_generation' => (clone $docs())->get(['created_at'])
                    ->groupBy(fn ($d) => optional($d->created_at)->format('Y-m') ?? 'n/a')
                    ->map(fn ($g, $m) => ['label' => (string) $m, 'count' => $g->count()])
                    ->sortKeys()->values()->take(-12)->all(),
                'verification_trend' => (clone $verifs())->get(['verified_at'])
                    ->groupBy(fn ($v) => optional($v->verified_at)->format('Y-m') ?? 'n/a')
                    ->map(fn ($g, $m) => ['label' => (string) $m, 'count' => $g->count()])
                    ->sortKeys()->values()->take(-12)->all(),
                'certificate_distribution' => (clone $docs())->with('certificateType:id,name')->get()
                    ->groupBy(fn ($d) => $d->certificateType?->name ?? 'Other')
                    ->map(fn ($g, $name) => ['label' => (string) $name, 'count' => $g->count()])->values()->all(),
            ],
        ];
    }
}
