<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Models\GeneratedDocument;
use App\Modules\Documents\Models\Template;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Exceptions\DomainException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;

/**
 * Document generation engine. Generated documents are IMMUTABLE snapshots (rendered
 * HTML + variable snapshot + template version). Each document receives a platform
 * Identity for QR + verification. Regeneration creates a NEW version (parent_id +
 * incremented version) — nothing is ever overwritten. Audit + Timeline + Comms.
 */
class GenerationService extends BaseService
{
    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly VariableResolver $variables,
        private readonly ActivityLogger $activity,
        private readonly StudentTimelineService $studentTimeline,
        private readonly StaffTimelineService $staffTimeline,
        private readonly DocumentHooks $hooks,
    ) {}

    /**
     * Render a template for a subject WITHOUT persisting (preview only).
     *
     * @return array<string, mixed>
     */
    public function preview(Template $template, string $subjectKind, ?int $subjectId): array
    {
        $vars = $this->variables->resolve((int) $template->school_id, $subjectKind, $subjectId);
        $html = $this->variables->merge($this->fullHtml($template), $vars);

        return ['html' => $html, 'variables' => $vars];
    }

    /**
     * Generate an immutable document from a template.
     *
     * @param  array<string, mixed>  $data
     */
    public function generate(Template $template, string $subjectKind, ?int $subjectId, array $data = []): GeneratedDocument
    {
        return $this->transaction(function () use ($template, $subjectKind, $subjectId, $data): GeneratedDocument {
            $schoolId = (int) $template->school_id;
            $subjectType = $this->subjectClass($subjectKind);

            // Create the row first so the platform Identity is minted (QR/verification).
            $document = GeneratedDocument::query()->create([
                'school_id' => $schoolId,
                'document_number' => $this->numbers->next('documents.number', $schoolId, Auth::id()),
                'certificate_type_id' => $template->certificate_type_id ?? ($data['certificate_type_id'] ?? null),
                'template_id' => $template->id,
                'template_version' => $template->version,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'version' => 1,
                'status' => DocumentStatus::Issued->value,
                'issued_by' => Auth::id(),
                'issued_to' => $data['issued_to'] ?? null,
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'remarks' => $data['remarks'] ?? null,
                'signatures' => $data['signatures'] ?? null,
            ]);

            return $this->render($document, $template, $subjectKind, $subjectId);
        });
    }

    /**
     * Regenerate a document as a NEW version (previous preserved).
     */
    public function regenerate(GeneratedDocument $document): GeneratedDocument
    {
        $template = Template::query()->findOrFail($document->template_id);

        return $this->transaction(function () use ($document, $template): GeneratedDocument {
            $latest = (int) GeneratedDocument::query()
                ->where('subject_type', $document->subject_type)->where('subject_id', $document->subject_id)
                ->where('certificate_type_id', $document->certificate_type_id)->max('version');

            $copy = GeneratedDocument::query()->create([
                'school_id' => $document->school_id,
                'document_number' => $this->numbers->next('documents.number', (int) $document->school_id, Auth::id()),
                'certificate_type_id' => $document->certificate_type_id,
                'template_id' => $template->id,
                'template_version' => $template->version,
                'subject_type' => $document->subject_type,
                'subject_id' => $document->subject_id,
                'version' => $latest + 1,
                'parent_id' => $document->id,
                'status' => DocumentStatus::Issued->value,
                'issued_by' => Auth::id(),
                'issued_to' => $document->issued_to,
                'issue_date' => now()->toDateString(),
                'signatures' => $document->signatures,
            ]);

            $kind = $this->subjectKind($document->subject_type);

            return $this->render($copy, $template, $kind, $document->subject_id, true);
        });
    }

    private function render(GeneratedDocument $document, Template $template, string $subjectKind, ?int $subjectId, bool $regen = false): GeneratedDocument
    {
        $document->refresh(); // ensure identity_id populated by HasIdentity
        $identity = $document->identity_id !== null ? Identity::query()->find($document->identity_id) : null;
        $vars = $this->variables->resolve((int) $document->school_id, $subjectKind, $subjectId, $identity);

        $document->rendered_html = $this->variables->merge($this->fullHtml($template), $vars);
        $document->variables = $vars;
        $document->verification_code = $identity?->identity_number;
        $document->save();

        $action = $regen ? 'documents.regenerated' : 'documents.generated';
        $this->activity->record($action, "Document {$document->document_number}", $document, [
            'version' => $document->version, 'template_version' => $template->version,
        ], (int) $document->school_id, 'documents');

        $this->timeline($document, $subjectKind);
        $this->hooks->certificateIssued((int) $document->school_id, "Document {$document->document_number} issued.");
        $this->hooks->documentReady((int) $document->school_id, "Document {$document->document_number} is ready.");

        return $document->refresh();
    }

    private function timeline(GeneratedDocument $document, string $subjectKind): void
    {
        if ($document->subject_id === null) {
            return;
        }
        $event = 'documents.issued';
        if ($subjectKind === 'student') {
            $this->studentTimeline->record((int) $document->subject_id, $event, 'Document issued', $document->document_number, ['document_id' => $document->id]);
        } elseif ($subjectKind === 'staff') {
            $this->staffTimeline->record((int) $document->subject_id, $event, 'Document issued', $document->document_number, ['document_id' => $document->id]);
        }
    }

    private function fullHtml(Template $template): string
    {
        return trim((string) $template->header)."\n".(string) $template->html."\n".trim((string) $template->footer);
    }

    /** @return class-string */
    private function subjectClass(string $kind): string
    {
        return match ($kind) {
            'staff' => Staff::class,
            'guardian' => Guardian::class,
            'student' => Student::class,
            default => throw new DomainException('Unknown subject kind.', 422, 'BAD_SUBJECT'),
        };
    }

    private function subjectKind(?string $class): string
    {
        return match ($class) {
            Staff::class => 'staff',
            Guardian::class => 'guardian',
            default => 'student',
        };
    }
}
