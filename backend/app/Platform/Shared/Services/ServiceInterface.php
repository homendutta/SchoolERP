<?php

declare(strict_types=1);

namespace App\Platform\Shared\Services;

/**
 * Marker contract for the Service layer.
 *
 * Services are the single home of business rules and workflows. They orchestrate
 * repositories, enforce data scope, emit domain events, call cross-cutting
 * Foundation services (audit, numbering, notification, media), and own
 * transaction boundaries.
 *
 * Concrete module services define their own use-case methods; this marker keeps
 * the layer explicit and discoverable.
 */
interface ServiceInterface {}
