<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Foundation\Identity\Enums\IdentityStatus;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Foundation\Identity\Support\Code128;
use App\Platform\Shared\Query\SearchBuilder;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The single source of truth for person identities. Business modules NEVER touch
 * Identity records — they request an identity for their owner and reference its
 * id. QR/barcode are derived dynamically and never stored as image files.
 */
class IdentityService
{
    private const NUMBER_KEY = 'identity_number';

    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * Idempotently ensure an owner has an Identity. Re-issuing is impossible:
     * an existing identity is returned unchanged (identity is permanent).
     */
    public function ensureFor(Model $owner, IdentityType $type, ?string $manualNumber = null): Identity
    {
        $existing = Identity::query()
            ->where('owner_type', $owner::class)
            ->where('owner_id', $owner->getKey())
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($owner, $type, $manualNumber): Identity {
            $schoolId = $owner->getAttribute('school_id');

            $number = $manualNumber ?: $this->numbers->next(self::NUMBER_KEY, $schoolId);
            if ($manualNumber !== null) {
                $this->numbers->reserve(self::NUMBER_KEY, $number, $schoolId);
            }

            $publicId = $this->uniquePublicIdentifier();

            $identity = new Identity([
                'school_id' => $schoolId,
                'identity_number' => $number,
                'identity_type' => $type->value,
                'owner_type' => $owner::class,
                'owner_id' => $owner->getKey(),
                'public_identifier' => $publicId,
                'barcode_value' => $number,
                'status' => IdentityStatus::Active->value,
                'created_by' => Auth::id(),
            ]);
            $identity->qr_payload = $this->buildPayload($number, $type, $schoolId, $publicId);
            $identity->save();

            $this->activity->record('identity.created', "Identity {$number} issued for {$type->label()}", $identity, [
                'identity_type' => $type->value,
            ], $schoolId, 'identity');

            return $identity;
        });
    }

    /**
     * Regenerate the DERIVED data (QR payload + barcode value) without ever
     * touching the immutable identity_number / public_identifier / owner.
     */
    public function regenerate(Identity $identity): Identity
    {
        $type = $identity->identity_type instanceof IdentityType ? $identity->identity_type : IdentityType::from((string) $identity->identity_type);

        $identity->forceFill([
            'qr_payload' => $this->buildPayload($identity->identity_number, $type, $identity->school_id, $identity->public_identifier),
            'barcode_value' => $identity->identity_number,
            'updated_by' => Auth::id(),
        ])->save();

        $this->activity->record('identity.regenerated', "Identity {$identity->identity_number} regenerated", $identity, [], $identity->school_id, 'identity');

        return $identity->refresh();
    }

    public function setStatus(Identity $identity, IdentityStatus $status): Identity
    {
        $identity->forceFill(['status' => $status->value, 'updated_by' => Auth::id()])->save();

        $action = $status === IdentityStatus::Disabled ? 'identity.disabled' : 'identity.enabled';
        $this->activity->record($action, "Identity {$identity->identity_number} {$status->value}", $identity, [], $identity->school_id, 'identity');

        return $identity->refresh();
    }

    /** Look up an identity by its public identifier or identity number. */
    public function lookup(string $identifier): ?Identity
    {
        return Identity::query()
            ->where('public_identifier', $identifier)
            ->orWhere('identity_number', $identifier)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function search(array $params): LengthAwarePaginator
    {
        $query = SearchBuilder::make(Identity::query()->with('owner'))
            ->text(['identity_number', 'public_identifier'], $params['q'] ?? null)
            ->applyDefinitions([
                'identity_number' => ['type' => 'text', 'columns' => ['identity_number']],
                'public_identifier' => ['type' => 'text', 'columns' => ['public_identifier']],
                'identity_type' => ['type' => 'enum', 'enum' => IdentityType::class],
                'status' => ['type' => 'enum', 'enum' => IdentityStatus::class],
                'owner' => ['type' => 'relation', 'relation' => 'owner', 'columns' => ['name']],
            ], (array) ($params['search'] ?? []))
            ->build();

        if (! empty($params['filter']['identity_type'])) {
            $query->where('identity_type', $params['filter']['identity_type']);
        }
        if (! empty($params['filter']['status'])) {
            $query->where('status', $params['filter']['status']);
        }
        if (! empty($params['filter']['school_id'])) {
            $query->where('school_id', $params['filter']['school_id']);
        }

        return $query->latest('id')->paginate(min((int) ($params['per_page'] ?? 15), 100));
    }

    /** Dynamic QR image (SVG) encoding the stored payload. */
    public function qrSvg(Identity $identity, int $size = 220): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd);
        $writer = new Writer($renderer);

        return $writer->writeString((string) json_encode($identity->qr_payload));
    }

    /** Dynamic barcode image (SVG) of the stored barcode value. */
    public function barcodeSvg(Identity $identity): string
    {
        return Code128::svg((string) $identity->barcode_value);
    }

    /**
     * @return array{identity:string, type:string, school:int|null, public_id:string}
     */
    private function buildPayload(string $number, IdentityType $type, ?int $schoolId, string $publicId): array
    {
        // SECURITY: never include internal database ids.
        return [
            'identity' => $number,
            'type' => $type->value,
            'school' => $schoolId,
            'public_id' => $publicId,
        ];
    }

    private function uniquePublicIdentifier(): string
    {
        do {
            $candidate = 'id_'.Str::lower(bin2hex(random_bytes(12)));
        } while (Identity::query()->where('public_identifier', $candidate)->exists());

        return $candidate;
    }
}
