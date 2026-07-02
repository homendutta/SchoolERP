<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Resources;

use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Generic passthrough resource for the library's simple reference entities
 * (publishers, authors, categories, locations, fine rules, inventory checks).
 * Enum-cast attributes are serialised to their scalar value.
 */
class SimpleResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource->attributesToArray();

        foreach ($data as $key => $value) {
            $attr = $this->resource->getAttribute($key);
            if ($attr instanceof \BackedEnum) {
                $data[$key] = $attr->value;
            }
        }

        return $data;
    }
}
