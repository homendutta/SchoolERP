<?php

declare(strict_types=1);

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;

/** A user-saved report (reusable filters / columns / sorting). */
class SavedReport extends Model
{
    protected $table = 'report_saved';

    protected $fillable = ['school_id', 'user_id', 'report_key', 'name', 'filters', 'columns', 'sort'];

    protected function casts(): array
    {
        return ['filters' => 'array', 'columns' => 'array', 'sort' => 'array'];
    }
}
