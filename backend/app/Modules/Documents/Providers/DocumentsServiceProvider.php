<?php

declare(strict_types=1);

namespace App\Modules\Documents\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Certificate & Document Management module (Sprint 20).
 *
 * The single source of truth for every official document the ERP issues:
 * configurable categories + certificate types, VERSIONED templates, a reusable
 * variable/merge engine, document generation with IMMUTABLE snapshots, regeneration
 * as new versions, digital-signature references (Media Platform), dynamic QR +
 * public verification (Identity Platform — QR images are never stored), issuance
 * history, bulk generation via queued jobs, search and a dashboard. Every
 * generation is audited; the Timeline records issuance/regeneration; Communication
 * notifies; public verification never exposes sensitive personal information.
 *
 * Designed so DigiLocker, government eSign, Aadhaar eKYC, blockchain verification,
 * AI document generation, multi-language + multi-campus templates and digital
 * wallet credentials require no structural change.
 */
class DocumentsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Documents';
}
