<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Support\Biometric;

/**
 * Registry of biometric connectors keyed by vendor/device type. New vendors are
 * registered here without changing the Attendance Engine.
 */
class ConnectorRegistry
{
    /** @var array<string, BiometricConnector> */
    private array $connectors = [];

    public function register(BiometricConnector $connector): void
    {
        $this->connectors[$connector->vendor()] = $connector;
    }

    public function get(string $vendor): ?BiometricConnector
    {
        return $this->connectors[$vendor] ?? null;
    }

    public function has(string $vendor): bool
    {
        return isset($this->connectors[$vendor]);
    }

    /** @return array<int, string> */
    public function vendors(): array
    {
        return array_keys($this->connectors);
    }
}
