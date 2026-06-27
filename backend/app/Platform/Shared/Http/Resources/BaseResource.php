<?php

declare(strict_types=1);

namespace App\Platform\Shared\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base API resource (response transformer) for every module.
 *
 * Resources shape outbound data only — they never contain business logic or
 * data access. Concrete module resources extend this and implement toArray().
 */
abstract class BaseResource extends JsonResource {}
