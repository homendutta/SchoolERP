<?php

declare(strict_types=1);

namespace App\Modules\Administration\Services;

use App\Modules\Administration\Support\ImporterRegistry;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Http\UploadedFile;

/**
 * Generic Import pipeline: Upload -> Validate -> Preview -> Execute -> Summary.
 * Modules plug in their own importers via the ImporterRegistry.
 */
class ImportService extends BaseService
{
    public function __construct(private readonly ImporterRegistry $registry) {}

    /**
     * Parse an uploaded CSV (first row = headers) into associative rows.
     *
     * @return array<int, array<string, string>>
     */
    public function parseCsv(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle) ?: [];
        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, array_pad($line, count($headers), null));
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<int, string>> row errors keyed by index
     */
    public function validate(string $key, array $rows): array
    {
        $importer = $this->registry->get($key);

        return $importer?->validate($rows) ?? [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int> summary
     */
    public function execute(string $key, array $rows): array
    {
        $importer = $this->registry->get($key);
        if ($importer === null) {
            throw BusinessRuleException::make("No importer is registered for '{$key}'.", 'IMPORTER_NOT_FOUND');
        }

        return $this->transaction(fn () => $importer->execute($rows));
    }
}
