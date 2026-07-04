<?php

declare(strict_types=1);

namespace App\Modules\Reports\Enums;

/** Output format an execution can be rendered/exported to. */
enum ReportFormat: string
{
    case Pdf = 'pdf';
    case Xlsx = 'xlsx';
    case Csv = 'csv';
    case Print = 'print';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return strtoupper($this->value);
    }
}
