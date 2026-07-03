<?php

declare(strict_types=1);

namespace App\Modules\Portal\Services;

use App\Modules\Administration\Models\User;
use App\Platform\Foundation\Audit\ActivityLogger;
use Illuminate\Support\Facades\Hash;

/**
 * Portal self-service profile. Updates the linked person's contact fields
 * (Guardian / Student / Staff) and the user's password/photo. Images use the
 * Media Platform (media id). No business logic beyond the update + audit.
 */
class PortalProfileService
{
    public function __construct(
        private readonly PortalContextService $context,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function show(User $user): array
    {
        $ctx = $this->context->resolve($user);
        $person = $ctx->guardian ?? $ctx->students->first() ?? $ctx->staff;

        return [
            'role' => $ctx->role->value,
            'user_id' => $user->id,
            'name' => $person?->getAttribute('name'),
            'email' => $person?->getAttribute('email') ?? $user->email,
            'phone' => $person?->getAttribute('phone'),
            'address' => $person?->getAttribute('address'),
            'photo_media_id' => $person?->getAttribute('photo_media_id'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $user, array $data): array
    {
        $ctx = $this->context->resolve($user);
        $person = $ctx->guardian ?? $ctx->students->first() ?? $ctx->staff;

        if ($person !== null) {
            foreach (['phone', 'address', 'photo_media_id'] as $field) {
                if (array_key_exists($field, $data) && in_array($field, $person->getFillable(), true)) {
                    $person->setAttribute($field, $data[$field]);
                }
            }
            $person->save();
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make((string) $data['password']);
            $user->save();
        }

        $this->activity->record('portal.profile_updated', 'Portal profile updated', $user, [], (int) $user->school_id, 'portal');

        return $this->show($user->refresh());
    }
}
