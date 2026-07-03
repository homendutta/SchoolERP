<?php

declare(strict_types=1);

namespace App\Modules\Lms\Enums;

/** Learning-material file kind (the file itself lives in the Media Platform). */
enum MaterialType: string
{
    case Pdf = 'pdf';
    case Docx = 'docx';
    case Ppt = 'ppt';
    case Xls = 'xls';
    case Image = 'image';
    case Audio = 'audio';
    case Video = 'video';
    case Zip = 'zip';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return strtoupper($this->value);
    }
}
