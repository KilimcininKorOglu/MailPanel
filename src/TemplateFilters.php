<?php

declare(strict_types=1);

namespace App;

class TemplateFilters
{
    /**
     * Converts status/boolean values to emoji indicators.
     */
    public static function localize(string|bool $data): string
    {
        $data = strtolower((string) $data);
        return match ($data) {
            'yes', 'active', 'true' => '✅',
            'no', 'disabled', 'false' => '❌',
            default => $data,
        };
    }

    /**
     * Converts bytes to megabytes for display.
     */
    public static function asMegabytes(string|int $data): string
    {
        if (is_numeric($data)) {
            $mb = (int) $data / 1048576;
            return number_format($mb, 0, '.', '');
        }
        return (string) $data;
    }
}
