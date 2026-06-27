<?php

declare(strict_types=1);

namespace App\Platform\Shared\Actions;

/**
 * Marker contract for Actions — single-purpose, invokable units that encapsulate
 * a complex business operation (e.g., reset a number sequence, send a test
 * email). Keeps Services/Controllers thin.
 */
interface Action {}
