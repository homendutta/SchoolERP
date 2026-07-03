<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Models\Template;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Document templates. Templates are VERSIONED: a new version is a NEW row that
 * links to the previous via parent_id; old versions remain available. Plain
 * updates edit the current row's metadata; `createVersion` snapshots a new one.
 */
class TemplateService extends BaseCrudService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    protected function model(): string
    {
        return Template::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('certificateType:id,name');
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'category_id', 'certificate_type_id', 'code', 'version', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'version'];
    }

    /**
     * Create a new immutable version from an existing template, preserving the old.
     *
     * @param  array<string, mixed>  $changes
     */
    public function createVersion(Template $template, array $changes): Template
    {
        return $this->transaction(function () use ($template, $changes): Template {
            $data = array_merge($template->only([
                'school_id', 'category_id', 'certificate_type_id', 'name', 'code', 'html', 'header',
                'footer', 'variables', 'logo_media_id', 'watermark_media_id', 'background_media_id',
                'margins', 'orientation', 'paper_size',
            ]), $changes);

            $latest = (int) Template::query()->where('code', $template->code)
                ->where('school_id', $template->school_id)->max('version');
            $data['version'] = $latest + 1;
            $data['parent_id'] = $template->id;

            /** @var Template $version */
            $version = Template::query()->create($data);

            $this->activity->record('documents.template_versioned', "Template version {$version->version}", $version, [
                'parent_id' => $template->id,
            ], (int) $version->school_id, 'documents');

            return $version;
        });
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        $model = parent::create($data);
        $this->activity->record('documents.template_created', 'Template created', $model, [], (int) $model->getAttribute('school_id'), 'documents');

        return $model;
    }
}
