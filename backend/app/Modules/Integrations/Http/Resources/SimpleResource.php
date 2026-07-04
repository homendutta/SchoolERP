<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Resources;

use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Generic passthrough resource for the Integrations module. Enum-cast attributes
 * become their scalar value; the encrypted `config`/`secret` are NEVER exposed —
 * they are stripped from the output.
 */
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

        // Never leak secrets/credentials over the API.
        unset($data['config'], $data['secret']);
        if (array_key_exists('config', $this->resource->getAttributes()) || $this->resource->getAttribute('config') !== null) {
            $data['has_config'] = ! empty($this->resource->getAttribute('config'));
        }

        foreach ($this->resource->getRelations() as $name => $relation) {
            $data[$name] = $relation;
        }

        return $data;
    }
}
