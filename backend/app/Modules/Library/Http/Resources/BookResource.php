<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Resources;

use App\Modules\Library\Models\Book;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Book
 */
class BookResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'isbn' => $this->isbn,
            'edition' => $this->edition,
            'language' => $this->language,
            'publication_year' => $this->publication_year,
            'description' => $this->description,
            'publisher_id' => $this->publisher_id,
            'publisher' => $this->whenLoaded('publisher', fn () => $this->publisher?->name),
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'cover_media_id' => $this->cover_media_id,
            'authors' => $this->whenLoaded('authors', fn () => $this->authors->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])->values()),
            'copies_count' => $this->whenCounted('copies'),
            'status' => $this->status->value,
        ];
    }
}
