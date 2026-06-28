<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Support\Biometric;

/**
 * A vendor-specific biometric connector. ALL device-specific parsing lives in
 * connectors — the Attendance Engine stays vendor-independent. Adding a new
 * vendor means adding a connector, never touching the engine.
 *
 * @phpstan-type NormalisedEvent array{identity_number:string, event_time:string, direction:?string}
 */
interface BiometricConnector
{
    /** Unique vendor/device-type key (matches biometric_devices.device_type). */
    public function vendor(): string;

    /**
     * Normalise a raw device payload into a list of generic attendance events.
     *
     * @param  array<string, mixed>  $raw
     * @return array<int, array{identity_number:string, event_time:string, direction:?string}>
     */
    public function normalize(array $raw): array;
}
