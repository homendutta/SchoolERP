<?php

declare(strict_types=1);

namespace App\Platform\Shared\Query;

use Illuminate\Database\Eloquent\Builder;

/**
 * Applies sorting from a `sort` param against a whitelist. A leading "-" means
 * descending (e.g., "-created_at"). Falls back to a default ordering.
 */
final class SortBuilder
{
    /**
     * @param  array<int, string>  $allowed
     */
    public static function apply(
        Builder $query,
        ?string $sort,
        array $allowed,
        string $default = 'id',
        string $defaultDirection = 'desc'
    ): Builder {
        $sort = trim((string) $sort);

        if ($sort !== '') {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $column = ltrim($sort, '-');
            if (in_array($column, $allowed, true)) {
                return $query->orderBy($column, $direction);
            }
        }

        return $query->orderBy($default, $defaultDirection);
    }
}
