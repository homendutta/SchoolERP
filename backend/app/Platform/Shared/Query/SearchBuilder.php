<?php

declare(strict_types=1);

namespace App\Platform\Shared\Query;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;

/**
 * Typed search builder. Beyond plain LIKE, it supports:
 *   - text     : LIKE across one or more columns
 *   - enum     : exact match validated against a backed enum
 *   - date     : exact date, or a "from..to" range
 *   - numeric  : exact value, or a "min..max" range
 *   - relation : whereHas() LIKE across related columns
 *
 * Used by BaseCrudService and any module needing reusable search.
 */
final class SearchBuilder
{
    private function __construct(private Builder $query) {}

    public static function make(Builder $query): self
    {
        return new self($query);
    }

    public function build(): Builder
    {
        return $this->query;
    }

    /** LIKE across a set of columns for a single term. */
    public function text(array $columns, mixed $term): self
    {
        $term = trim((string) $term);
        if ($term === '' || $columns === []) {
            return $this;
        }

        $this->query->where(function (Builder $q) use ($term, $columns): void {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });

        return $this;
    }

    /** Exact match validated against a backed enum class. */
    public function enum(string $column, mixed $value, string $enumClass): self
    {
        if ($this->blank($value) || ! is_subclass_of($enumClass, BackedEnum::class)) {
            return $this;
        }

        $case = $enumClass::tryFrom((string) $value);
        if ($case !== null) {
            $this->query->where($column, $case->value);
        }

        return $this;
    }

    /** Exact date or "from..to" range (inclusive). */
    public function date(string $column, mixed $value): self
    {
        if ($this->blank($value)) {
            return $this;
        }

        [$from, $to] = $this->range($value);
        if ($to !== null) {
            $this->query->whereBetween($column, [$from.' 00:00:00', $to.' 23:59:59']);
        } else {
            $this->query->whereDate($column, $from);
        }

        return $this;
    }

    /** Exact numeric or "min..max" range (inclusive). */
    public function numeric(string $column, mixed $value): self
    {
        if ($this->blank($value)) {
            return $this;
        }

        [$min, $max] = $this->range($value);
        if ($max !== null) {
            $this->query->whereBetween($column, [(float) $min, (float) $max]);
        } else {
            $this->query->where($column, $min);
        }

        return $this;
    }

    /** LIKE across columns of a related model. */
    public function relation(string $relation, array $columns, mixed $term): self
    {
        $term = trim((string) $term);
        if ($term === '' || $columns === []) {
            return $this;
        }

        $this->query->whereHas($relation, function (Builder $q) use ($term, $columns): void {
            $q->where(function (Builder $inner) use ($term, $columns): void {
                foreach ($columns as $column) {
                    $inner->orWhere($column, 'like', "%{$term}%");
                }
            });
        });

        return $this;
    }

    /**
     * Apply a definition map against request values.
     *
     * @param  array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>  $definitions
     * @param  array<string, mixed>  $values
     */
    public function applyDefinitions(array $definitions, array $values): self
    {
        foreach ($definitions as $field => $def) {
            $value = $values[$field] ?? null;
            if ($this->blank($value)) {
                continue;
            }

            match ($def['type']) {
                'text' => $this->text($def['columns'] ?? [$field], $value),
                'enum' => $this->enum($field, $value, $def['enum'] ?? ''),
                'date' => $this->date($field, $value),
                'numeric' => $this->numeric($field, $value),
                'relation' => $this->relation($def['relation'] ?? $field, $def['columns'] ?? [], $value),
                default => null,
            };
        }

        return $this;
    }

    /** @return array{0: mixed, 1: mixed} [from, to|null] */
    private function range(mixed $value): array
    {
        if (is_array($value)) {
            return [$value['from'] ?? $value['min'] ?? ($value[0] ?? null), $value['to'] ?? $value['max'] ?? ($value[1] ?? null)];
        }

        if (is_string($value) && str_contains($value, '..')) {
            [$a, $b] = array_pad(explode('..', $value, 2), 2, null);

            return [$a === '' ? null : $a, $b === '' ? null : $b];
        }

        return [$value, null];
    }

    private function blank(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }
}
