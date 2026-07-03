<?php

declare(strict_types=1);

namespace App\Modules\Cms\Enums;

/** Public form kinds. Submissions flow into the ERP via the Communication Engine. */
enum FormType: string
{
    case Contact = 'contact';
    case AdmissionEnquiry = 'admission_enquiry';
    case GeneralEnquiry = 'general_enquiry';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
