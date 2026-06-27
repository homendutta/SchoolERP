<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Core school identity. Focused concerns live in dedicated one-to-one relations
 * (branding, contact, regional, academic) rather than on this table.
 */
class School extends Model
{
    protected $fillable = [
        'name', 'short_name', 'code', 'motto', 'about',
        'established_year', 'registration_number', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function branding(): HasOne
    {
        return $this->hasOne(SchoolBranding::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(SchoolContact::class);
    }

    public function regional(): HasOne
    {
        return $this->hasOne(SchoolRegional::class);
    }

    public function academic(): HasOne
    {
        return $this->hasOne(SchoolAcademicSetting::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Ensure all one-to-one settings rows exist (creating defaults as needed). */
    public function loadSettings(): self
    {
        $this->branding()->firstOrCreate([]);
        $this->contact()->firstOrCreate([]);
        $this->regional()->firstOrCreate([]);
        $this->academic()->firstOrCreate([]);

        return $this->load(['branding', 'contact', 'regional', 'academic']);
    }
}
