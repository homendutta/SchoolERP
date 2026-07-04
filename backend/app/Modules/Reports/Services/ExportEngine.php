<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Support\ExportResult;
use App\Modules\Reports\Support\ReportDefinition;

/**
 * The one centralized export engine. Every module exports through here — no
 * duplicate CSV/Excel code anywhere else. Drivers are pluggable: CSV and an
 * Excel-compatible SpreadsheetML writer ship (dependency-free); a binary XLSX or
 * PDF driver can be registered later without touching callers.
 */
class ExportEngine
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function export(ReportDefinition $definition, array $rows, string $format): ExportResult
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '_', $definition->key);

        return match ($format) {
            'xlsx' => $this->excel($definition, $rows, (string) $slug),
            default => $this->csv($definition, $rows, (string) $slug),
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function csv(ReportDefinition $definition, array $rows, string $slug): ExportResult
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_values($definition->columns));
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($k) => (string) ($row[$k] ?? ''), array_keys($definition->columns)));
        }
        rewind($handle);
        $content = (string) stream_get_contents($handle);
        fclose($handle);

        return new ExportResult("{$slug}.csv", 'text/csv', $content, count($rows));
    }

    /**
     * SpreadsheetML 2003 — a valid, dependency-free workbook every version of
     * Excel opens. (A binary .xlsx driver can replace this without caller changes.)
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function excel(ReportDefinition $definition, array $rows, string $slug): ExportResult
    {
        $esc = fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_XML1);
        $cells = fn (array $values): string => implode('', array_map(
            fn ($v) => '<Cell><Data ss:Type="String">'.$esc($v).'</Data></Cell>', $values
        ));

        $header = '<Row>'.$cells(array_values($definition->columns)).'</Row>';
        $body = '';
        foreach ($rows as $row) {
            $body .= '<Row>'.$cells(array_map(fn ($k) => $row[$k] ?? '', array_keys($definition->columns))).'</Row>';
        }

        $xml = '<?xml version="1.0"?>'
            .'<?mso-application progid="Excel.Sheet"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Worksheet ss:Name="Report"><Table>'.$header.$body.'</Table></Worksheet></Workbook>';

        return new ExportResult("{$slug}.xls", 'application/vnd.ms-excel', $xml, count($rows));
    }
}
