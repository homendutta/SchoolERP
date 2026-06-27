<?php

declare(strict_types=1);

namespace App\Platform\Shared\Query;

use Illuminate\Database\Eloquent\Builder;

/**
 * Applies equality / IN filters from request params against a whitelist of
 * allowed columns. Reusable across modules.
 */
final class FilterBuilder
{
    /**
     * @param  array<string, mixed>  $filters  request filter map (column => value)
     * @param  array<int, string>  $allowed  columns permitted to be filtered
     */
    public static function apply(Builder $query, array $filters, array $allowed): Builder
    {
        foreach ($filters as $column => $value) {
            if (! in_array($column, $allowed, true) || $value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query;
    }
}
