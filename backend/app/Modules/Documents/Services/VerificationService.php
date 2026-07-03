<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Enums\VerificationResult;
use App\Modules\Documents\Models\GeneratedDocument;
use App\Modules\Documents\Models\Verification;
use App\Platform\Foundation\Identity\IdentityService;

/**
 * Public + admin document verification. Verification ALWAYS resolves through the
 * Identity Platform (QR) or the document number / verification code. Every attempt
 * is logged; the public response exposes only the verification status + basic,
 * non-sensitive document details (never contact info or internal ids).
 */
class VerificationService
{
    public function __construct(private readonly IdentityService $identities) {}

    /**
     * @return array<string, mixed>
     */
    public function verify(string $method, string $identifier): array
    {
        $document = $this->resolve($method, $identifier);

        $result = $document === null
            ? VerificationResult::Invalid
            : ($document->status === DocumentStatus::Revoked ? VerificationResult::Revoked : VerificationResult::Valid);

        if ($document !== null) {
            Verification::query()->create([
                'school_id' => $document->school_id,
                'document_id' => $document->id,
                'method' => $method,
                'result' => $result->value,
                'identifier' => $identifier,
                'verified_at' => now(),
            ]);
        }

        return [
            'result' => $result->value,
            'verified' => $result === VerificationResult::Valid,
            'document' => $document !== null ? $this->publicDetails($document) : null,
        ];
    }

    private function resolve(string $method, string $identifier): ?GeneratedDocument
    {
        return match ($method) {
            'document_number' => GeneratedDocument::query()->where('document_number', $identifier)->first(),
            'code' => GeneratedDocument::query()->where('verification_code', $identifier)->first(),
            'qr' => $this->byQr($identifier),
            default => GeneratedDocument::query()
                ->where('document_number', $identifier)
                ->orWhere('verification_code', $identifier)->first(),
        };
    }

    private function byQr(string $identifier): ?GeneratedDocument
    {
        $identity = $this->identities->lookup($identifier);
        if ($identity === null) {
            return null;
        }

        return GeneratedDocument::query()->where('identity_id', $identity->id)->first();
    }

    /**
     * Non-sensitive public projection.
     *
     * @return array<string, mixed>
     */
    private function publicDetails(GeneratedDocument $document): array
    {
        $document->loadMissing('certificateType:id,name');
        $variables = $document->variables ?? [];

        return [
            'document_number' => $document->document_number,
            'certificate_type' => $document->certificateType?->name,
            'holder_name' => $variables['student.name'] ?? $variables['staff.name'] ?? $variables['guardian.name'] ?? null,
            'issue_date' => $document->issue_date?->toDateString(),
            'status' => $document->status->value,
            'version' => $document->version,
        ];
    }
}
