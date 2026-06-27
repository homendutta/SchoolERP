<?php

declare(strict_types=1);

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Models\School;
use App\Platform\Shared\Services\BaseService;

/**
 * Manages the single-tenant school profile across its focused settings tables
 * (general, branding, contact, regional, academic).
 */
class SchoolSettingsService extends BaseService
{
    public function current(): School
    {
        $school = School::query()->firstOrCreate(
            ['code' => 'DEFAULT'],
            ['name' => 'My School'],
        );

        return $school->loadSettings();
    }

    /**
     * @param  array<string, mixed>  $data  sections: general, branding, contact, regional, academic
     */
    public function update(array $data): School
    {
        return $this->transaction(function () use ($data): School {
            $school = $this->current();

            if (! empty($data['general'])) {
                $school->fill($data['general'])->save();
            }
            if (! empty($data['branding'])) {
                $school->branding()->update($data['branding']);
            }
            if (! empty($data['contact'])) {
                $school->contact()->update($data['contact']);
            }
            if (! empty($data['regional'])) {
                $school->regional()->update($data['regional']);
            }
            if (! empty($data['academic'])) {
                $school->academic()->update($data['academic']);
            }

            return $school->fresh()->loadSettings();
        });
    }
}
