<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable storage layout: room / rack / shelf / position. */
class LibraryLocation extends Model
{
    use SoftDeletes;

    protected $table = 'library_locations';

    protected $fillable = ['school_id', 'name', 'room', 'rack', 'shelf', 'position', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }
}
