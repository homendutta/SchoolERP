<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use SoftDeletes;

    protected $table = 'library_authors';

    protected $fillable = ['school_id', 'name', 'bio', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'library_book_author', 'author_id', 'book_id');
    }
}
