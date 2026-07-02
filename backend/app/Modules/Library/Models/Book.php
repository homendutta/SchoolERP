<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use App\Platform\Enums\RecordStatus;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A catalog entry (publication). Never borrowed — copies are. */
class Book extends Model
{
    use SoftDeletes;

    protected $table = 'library_books';

    protected $fillable = [
        'school_id', 'title', 'subtitle', 'isbn', 'edition', 'language',
        'publication_year', 'description', 'publisher_id', 'category_id', 'cover_media_id', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['publication_year' => 'integer', 'status' => RecordStatus::class];
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'library_book_author', 'book_id', 'author_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(Copy::class);
    }
}
