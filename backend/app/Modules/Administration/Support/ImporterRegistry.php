<?php

declare(strict_types=1);

namespace App\Modules\Administration\Support;

use App\Platform\Shared\Contracts\Importer;

/**
 * Registry of module importers that plug into the generic Import framework.
 * Other modules register their importers here as they are built (e.g.,
 * students). Empty by design in Sprint 2.
 */
class ImporterRegistry
{
    /** @var array<string, Importer> */
    private array $importers = [];

    public function register(Importer $importer): void
    {
        $this->importers[$importer->key()] = $importer;
    }

    public function has(string $key): bool
    {
        return isset($this->importers[$key]);
    }

    public function get(string $key): ?Importer
    {
        return $this->importers[$key] ?? null;
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->importers);
    }
}
