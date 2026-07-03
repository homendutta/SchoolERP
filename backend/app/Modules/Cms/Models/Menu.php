<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use App\Modules\Cms\Enums\MenuLocation;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;

/** A CMS-managed navigation item (header / footer / quick links), ordered + nestable. */
class Menu extends Model
{
    protected $table = 'cms_menus';

    protected $fillable = ['school_id', 'location', 'label', 'url', 'parent_id', 'sequence', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'sequence' => 0];

    protected function casts(): array
    {
        return ['sequence' => 'integer', 'location' => MenuLocation::class, 'status' => RecordStatus::class];
    }
}
