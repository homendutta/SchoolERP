<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Hostel\Enums\HostelGender;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A hostel. Code from the Number Generator; multiple per school. */
class Hostel extends Model
{
    use SoftDeletes;

    protected $table = 'hostels';

    protected $fillable = ['school_id', 'code', 'name', 'gender', 'address', 'description', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'gender' => 'boys'];

    protected function casts(): array
    {
        return ['gender' => HostelGender::class, 'status' => RecordStatus::class];
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }
}
