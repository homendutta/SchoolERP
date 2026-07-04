<?php

declare(strict_types=1);

namespace App\Modules\Reports\Http\Resources;

use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/** Generic passthrough resource for the Reports module (enums → value, relations included). */
class SimpleResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->resource->attributesToArray();

        foreach ($data as $key => $value) {
            $attr = $this->resource->getAttribute($key);
            if ($attr instanceof \BackedEnum) {
                $data[$key] = $attr->value;
            }
        }

        foreach ($this->resource->getRelations() as $name => $relation) {
            $data[$name] = $relation;
        }

        return $data;
    }
}
