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
            default => htmlspecialchars($data, ENT_QUOTES, 'UTF-8'),
        };
    }

    /**
     * Formats a megabyte value for display (0 = unlimited).
     */
    public static function asMegabytes(string|int $data): string
    {
        $value = (int) $data;
        if ($value <= 0) {
            return '0';
        }
        return number_format($value, 0, '.', ',');
    }
}
