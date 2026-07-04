<?php

declare(strict_types=1);

namespace App\Modules\Reports\Support;

/** A rendered export: filename, MIME type and the raw content to stream. */
final class ExportResult
{
    public function __construct(
        public readonly string $filename,
        public readonly string $mime,
        public readonly string $content,
        public readonly int $rowCount,
    ) {}
}
