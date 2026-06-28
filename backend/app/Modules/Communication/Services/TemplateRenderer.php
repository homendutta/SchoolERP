<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

/** Renders {{variable}} placeholders in a template subject/body. */
class TemplateRenderer
{
    /**
     * @param  array<string, scalar|null>  $variables
     */
    public function render(?string $text, array $variables): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        foreach ($variables as $key => $value) {
            $text = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], (string) ($value ?? ''), $text);
        }

        return $text;
    }
}
