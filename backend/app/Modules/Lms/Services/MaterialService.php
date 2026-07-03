<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Enums\MaterialType;
use App\Modules\Lms\Models\Material;
use Illuminate\Database\Eloquent\Model;

class MaterialService extends LmsContentService
{
    protected function model(): string
    {
        return Material::class;
    }

    protected function contentType(): string
    {
        return 'material';
    }

    protected function searchable(): array
    {
        return ['title', 'topic'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'subject_id', 'class_id', 'section_id', 'teacher_id', 'type', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'title' => ['type' => 'text', 'columns' => ['title', 'topic']],
            'type' => ['type' => 'enum', 'enum' => MaterialType::class],
            'status' => ['type' => 'enum', 'enum' => LmsStatus::class],
        ];
    }

    protected function onPublished(Model $model): void
    {
        $this->hooks->publish((int) $model->getAttribute('school_id'), 'lms.material_published', 'Material published', 'Material: '.$model->getAttribute('title'));
    }
}
