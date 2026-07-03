<?php

declare(strict_types=1);

namespace App\Modules\Documents\Jobs;

use App\Modules\Documents\Models\Template;
use App\Modules\Documents\Services\GenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued generation of a single document (used by bulk generation so large runs
 * do not block the request). Each job produces one immutable document.
 */
class GenerateDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly int $templateId,
        public readonly string $subjectKind,
        public readonly int $subjectId,
        public readonly array $data = [],
    ) {}

    public function handle(GenerationService $generation): void
    {
        $template = Template::query()->find($this->templateId);
        if ($template === null) {
            return;
        }

        $generation->generate($template, $this->subjectKind, $this->subjectId, $this->data);
    }
}
