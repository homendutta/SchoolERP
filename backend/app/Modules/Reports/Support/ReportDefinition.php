<?php

declare(strict_types=1);

namespace App\Modules\Reports\Support;

use Closure;

/**
 * A reusable report DEFINITION: what the report is + how to fetch its rows from
 * the owning module (read-only). The Reporting Engine applies filters, sorting,
 * grouping, pagination and totals over the rows the resolver returns — the
 * definition never contains business logic beyond reading its module's data.
 */
final class ReportDefinition
{
    /**
     * @param  array<string, string>  $columns  column key => label
     * @param  array<int, string>  $totals  numeric column keys to sum
     * @param  Closure(array<string, mixed>): array<int, array<string, mixed>>  $resolver
     */
    public function __construct(
        public readonly string $key,
        public readonly string $module,
        public readonly string $category,
        public readonly string $name,
        public readonly array $columns,
        public readonly Closure $resolver,
        public readonly array $totals = [],
        public readonly string $permission = 'reports.view',
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function resolve(array $params): array
    {
        return ($this->resolver)($params);
    }

    /**
     * @return array<string, mixed>
     */
    public function toCatalogArray(): array
    {
        return [
            'key' => $this->key,
            'module' => $this->module,
            'category' => $this->category,
            'name' => $this->name,
            'columns' => $this->columns,
            'totals' => $this->totals,
        ];
    }
}
