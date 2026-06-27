<?php

declare(strict_types=1);

namespace App\Platform\Shared\Actions;

/**
 * Convenience trait for Actions: resolve from the container and run in one call.
 * Concrete actions implement a `handle(...)` method.
 */
trait AsAction
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @param  mixed  ...$arguments
     */
    public static function run(...$arguments): mixed
    {
        return static::make()->handle(...$arguments);
    }
}
