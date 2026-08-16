<?php

namespace App\Support;

final class QuantityFormatter
{
    public static function format(int|float|string $quantity): string
    {
        $formatted = number_format((float) $quantity, 3, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
