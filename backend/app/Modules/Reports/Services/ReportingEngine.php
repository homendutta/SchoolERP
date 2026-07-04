<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Support\ReportDefinition;

/**
 * The one reusable Reporting Engine. Given a report definition + params it applies
 * filtering, sorting, grouping, pagination, totals and aggregation over the rows
 * the definition resolves from its owning module. It reads only — it never
 * modifies business data.
 */
class ReportingEngine
{
    /**
     * @param  array<string, mixed>  $params
     * @return array{columns:array<string,string>, rows:array<int,array<string,mixed>>, total:int, totals:array<string,float>, groups:?array<int,array<string,mixed>>, page:int, per_page:int}
     */
    public function run(ReportDefinition $definition, array $params): array
    {
        $rows = $definition->resolve($params);
        $rows = $this->filter($rows, is_array($params['filter'] ?? null) ? $params['filter'] : []);
        $rows = $this->sort($rows, isset($params['sort']) ? (string) $params['sort'] : null);

        $totals = $this->totals($rows, $definition->totals);
        $groups = isset($params['group_by']) ? $this->group($rows, (string) $params['group_by'], $definition->totals) : null;

        $total = count($rows);
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = (int) ($params['per_page'] ?? 50);
        $paged = $perPage > 0 ? array_slice($rows, ($page - 1) * $perPage, $perPage) : $rows;

        return [
            'columns' => $definition->columns,
            'rows' => array_values($paged),
            'total' => $total,
            'totals' => $totals,
            'groups' => $groups,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /** All rows (no pagination) — used by exports/print. @return array<int,array<string,mixed>> */
    public function rows(ReportDefinition $definition, array $params): array
    {
        $rows = $definition->resolve($params);
        $rows = $this->filter($rows, is_array($params['filter'] ?? null) ? $params['filter'] : []);

        return $this->sort($rows, isset($params['sort']) ? (string) $params['sort'] : null);
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @param  array<string,mixed>  $filters
     * @return array<int,array<string,mixed>>
     */
    private function filter(array $rows, array $filters): array
    {
        if ($filters === []) {
            return $rows;
        }

        return array_values(array_filter($rows, function (array $row) use ($filters): bool {
            foreach ($filters as $col => $value) {
                if ($value === '' || $value === null) {
                    continue;
                }
                $cell = $row[$col] ?? null;
                if (is_string($value) && is_string($cell)) {
                    if (stripos($cell, $value) === false) {
                        return false;
                    }
                } elseif ((string) $cell !== (string) $value) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<int,array<string,mixed>>
     */
    private function sort(array $rows, ?string $sort): array
    {
        if ($sort === null || $sort === '') {
            return $rows;
        }
        $desc = str_starts_with($sort, '-');
        $col = ltrim($sort, '-');

        usort($rows, function (array $a, array $b) use ($col): int {
            return ($a[$col] ?? null) <=> ($b[$col] ?? null);
        });

        return $desc ? array_reverse($rows) : $rows;
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @param  array<int,string>  $totalCols
     * @return array<string,float>
     */
    private function totals(array $rows, array $totalCols): array
    {
        $totals = [];
        foreach ($totalCols as $col) {
            $totals[$col] = round(array_sum(array_map(fn ($r) => (float) ($r[$col] ?? 0), $rows)), 2);
        }

        return $totals;
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @param  array<int,string>  $totalCols
     * @return array<int,array<string,mixed>>
     */
    private function group(array $rows, string $groupBy, array $totalCols): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $key = (string) ($row[$groupBy] ?? '—');
            $groups[$key] ??= ['group' => $key, 'count' => 0, 'totals' => array_fill_keys($totalCols, 0.0)];
            $groups[$key]['count']++;
            foreach ($totalCols as $col) {
                $groups[$key]['totals'][$col] += (float) ($row[$col] ?? 0);
            }
        }

        return array_values($groups);
    }
}
