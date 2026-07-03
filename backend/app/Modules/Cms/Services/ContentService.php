<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Model;

/**
 * Base for publishable CMS content (pages, notices, news, events, galleries,
 * videos, downloads). Handles the publish transition once: stamps published_at,
 * writes the Audit Log and (on first publish) a "published" Timeline event and a
 * Communication hook. Every create/update is audited.
 */
abstract class ContentService extends BaseCrudService
{
    public function __construct(
        protected readonly ActivityLogger $activity,
        protected readonly CmsHooks $hooks,
    ) {}

    /** Audit/log name + event prefix for this content type, e.g. "notice". */
    abstract protected function contentType(): string;

    /** Publish a "<type> published" Communication event (override per type). */
    protected function onPublished(Model $model): void {}

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $data = $this->stampPublish($data, null);
            $model = $this->model()::query()->create($data);

            $this->audit('cms.'.$this->contentType().'_created', 'created', $model);
            if ($this->isPublished($model)) {
                $this->firePublished($model);
            }

            return $model;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        return $this->transaction(function () use ($model, $data): Model {
            $wasPublished = $this->isPublished($model);
            $data = $this->stampPublish($data, $model);
            $model->fill($data)->save();

            $this->audit('cms.'.$this->contentType().'_updated', 'updated', $model);
            if (! $wasPublished && $this->isPublished($model->refresh())) {
                $this->firePublished($model);
            }

            return $model->refresh();
        });
    }

    /**
     * When a write publishes content, set published_at if not already set.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function stampPublish(array $data, ?Model $existing): array
    {
        $status = $data['status'] ?? $existing?->getAttribute('status')?->value ?? null;
        if ($status === ContentStatus::Published->value) {
            $current = $existing?->getAttribute('published_at');
            if ($current === null && empty($data['published_at'])) {
                $data['published_at'] = now();
            }
        }

        return $data;
    }

    protected function isPublished(Model $model): bool
    {
        $status = $model->getAttribute('status');

        return $status instanceof ContentStatus && $status === ContentStatus::Published;
    }

    private function firePublished(Model $model): void
    {
        // A "published" Timeline event (via the Audit/Timeline engine) + Communication.
        $this->audit('cms.'.$this->contentType().'_published', 'published', $model);
        $this->onPublished($model);
    }

    /** @param array<string, mixed> $props */
    protected function audit(string $action, string $description, Model $model, array $props = []): void
    {
        $this->activity->record($action, ucfirst($this->contentType()).' '.$description, $model, $props, (int) $model->getAttribute('school_id'), 'cms');
    }
}
