<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Models\Gallery;
use App\Modules\Cms\Models\GalleryImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Photo albums with their images (Media references). */
class GalleryService extends ContentService
{
    protected function model(): string
    {
        return Gallery::class;
    }

    protected function contentType(): string
    {
        return 'gallery';
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['images', 'category:id,name']);
    }

    protected function searchable(): array
    {
        return ['title'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'category_id', 'status', 'featured'];
    }

    protected function sortable(): array
    {
        return ['id', 'title', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'title' => ['type' => 'text', 'columns' => ['title']],
            'status' => ['type' => 'enum', 'enum' => ContentStatus::class],
        ];
    }

    public function find(int|string $id): Model
    {
        $query = $this->query();
        $this->withRelations($query);

        return $query->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        $images = $this->pullImages($data);

        return $this->transaction(function () use ($data, $images): Model {
            /** @var Gallery $gallery */
            $gallery = parent::create($data);
            $this->syncImages((int) $gallery->id, $images);

            return $gallery->load('images');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        $images = $this->pullImages($data);

        return $this->transaction(function () use ($model, $data, $images): Model {
            $gallery = parent::update($model, $data);
            if ($images !== null) {
                GalleryImage::query()->where('gallery_id', $gallery->getKey())->delete();
                $this->syncImages((int) $gallery->getKey(), $images);
            }

            return $gallery->load('images');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>|null
     */
    private function pullImages(array &$data): ?array
    {
        if (! array_key_exists('images', $data)) {
            return null;
        }
        $images = $data['images'];
        unset($data['images']);

        return is_array($images) ? $images : [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $images
     */
    private function syncImages(int $galleryId, array $images): void
    {
        foreach (array_values($images) as $i => $row) {
            GalleryImage::query()->create([
                'gallery_id' => $galleryId,
                'media_id' => $row['media_id'],
                'caption' => $row['caption'] ?? null,
                'sequence' => $row['sequence'] ?? $i,
            ]);
        }
    }
}
