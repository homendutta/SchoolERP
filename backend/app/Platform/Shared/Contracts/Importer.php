<?php

declare(strict_types=1);

namespace App\Platform\Shared\Contracts;

/**
 * Contract for module importers that plug into the generic Import framework:
 * Upload -> Validate -> Preview -> Execute -> Summary.
 *
 * @phpstan-type Row array<string, mixed>
 */
interface Importer
{
    /** Unique import key, e.g. "students". */
    public function key(): string;

    /**
     * Validate parsed rows. Returns a list of row errors keyed by row index.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<int, string>>
     */
    public function validate(array $rows): array;

    /**
     * Persist the validated rows. Returns a summary (created/updated/skipped).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    public function execute(array $rows): array;
}
