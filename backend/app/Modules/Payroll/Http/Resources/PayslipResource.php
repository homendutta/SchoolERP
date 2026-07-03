<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Resources;

use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * A payslip as structured data (no PDF rendering). Includes the earnings /
 * deductions / employer lines, the attendance & leave summary and a QR payload
 * derived from the employee's Identity (Identity Platform). Internal database ids
 * are never placed in the QR — only the employee's identity number + payslip
 * number + period.
 */
class PayslipResource extends BaseResource
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

        $identity = $this->resource->employee?->identity ?? null;
        $data['qr'] = [
            'payslip_number' => $this->resource->payslip_number,
            'identity_number' => $identity?->identity_number,
            'public_identifier' => $identity?->public_identifier,
            'period' => sprintf('%04d-%02d', $this->resource->period_year, $this->resource->period_month),
            'net_pay' => $this->resource->net_pay,
        ];

        return $data;
    }
}
