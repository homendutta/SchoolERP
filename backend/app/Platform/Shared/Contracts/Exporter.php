<?php

declare(strict_types=1);

namespace App\Platform\Shared\Contracts;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contract for export drivers (CSV / Excel / PDF) used by the generic Export
 * framework.
 */
interface Exporter
{
    /** Format key: csv | xlsx | pdf. */
    public function format(): string;

    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function download(string $filename, array $headings, iterable $rows): StreamedResponse;
}
