<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use App\Modules\Library\Enums\LibraryFineMode;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable library fine rule (Library calculates; Finance collects). */
class FineRule extends Model
{
    use SoftDeletes;

    protected $table = 'library_fine_rules';

    protected $fillable = ['school_id', 'name', 'borrower_type', 'mode', 'amount', 'grace_period_days', 'max_fine', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'mode' => 'daily'];

    protected function casts(): array
    {
        return [
            'mode' => LibraryFineMode::class,
            'amount' => 'float',
            'grace_period_days' => 'integer',
            'max_fine' => 'float',
            'status' => RecordStatus::class,
        ];
    }
}
