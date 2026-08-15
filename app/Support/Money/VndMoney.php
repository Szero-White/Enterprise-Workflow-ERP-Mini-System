<?php

namespace App\Support\Money;

use InvalidArgumentException;

final class VndMoney
{
    /** DECIMAL(15,2) can safely store up to 13 whole VND digits. */
    public const MAX_AMOUNT = 9_999_999_999_999;

    public const MAX_QUANTITY = 1_000_000;

    public static function toInteger(int|string $amount): int
    {
        if (is_int($amount)) {
            self::assertAmountRange($amount);

            return $amount;
        }

        $normalized = trim($amount);

        if (! preg_match('/^\d+(?:\.0{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('VND amount must be a whole-number value.');
        }

        $whole = (int) explode('.', $normalized, 2)[0];
        self::assertAmountRange($whole);

        return $whole;
    }

    public static function multiplyByQuantity(int|string $unitAmount, int|string $quantity): int
    {
        $unitAmount = self::toInteger($unitAmount);
        $milliQuantity = self::quantityToMilliUnits($quantity);

        if ($unitAmount === 0 || $milliQuantity === 0) {
            return 0;
        }

        // Check the range before multiplying. MAX_AMOUNT * 1000 is still well
        // inside a signed 64-bit integer, so this guard prevents PHP from ever
        // promoting an overflowing integer multiplication to a float.
        $maxMilliQuantity = intdiv(self::MAX_AMOUNT * 1000, $unitAmount);

        if ($milliQuantity > $maxMilliQuantity) {
            throw new InvalidArgumentException('Calculated VND amount exceeds the supported database range.');
        }

        $whole = intdiv($milliQuantity, 1000);
        $fraction = $milliQuantity % 1000;

        // Quantities use thousandths. Money remains integer VND and fractional
        // line values are rounded half-up to the nearest đồng.
        return ($unitAmount * $whole)
            + intdiv(($unitAmount * $fraction) + 500, 1000);
    }

    public static function add(int $left, int $right): int
    {
        self::assertAmountRange($left);
        self::assertAmountRange($right);

        if ($left > self::MAX_AMOUNT - $right) {
            throw new InvalidArgumentException('Calculated VND amount exceeds the supported database range.');
        }

        return $left + $right;
    }

    private static function quantityToMilliUnits(int|string $quantity): int
    {
        if (is_int($quantity)) {
            self::assertQuantityRange($quantity);

            return $quantity * 1000;
        }

        $normalized = trim($quantity);

        if (! preg_match('/^(\d+)(?:\.(\d{1,3}))?$/', $normalized, $matches)) {
            throw new InvalidArgumentException('Quantity must have at most three decimal places.');
        }

        $whole = (int) $matches[1];
        self::assertQuantityRange($whole);

        $fraction = str_pad($matches[2] ?? '', 3, '0');
        $milliQuantity = ($whole * 1000) + (int) $fraction;

        if ($milliQuantity > self::MAX_QUANTITY * 1000) {
            throw new InvalidArgumentException('Quantity exceeds the supported range.');
        }

        return $milliQuantity;
    }

    private static function assertAmountRange(int $amount): void
    {
        if ($amount < 0 || $amount > self::MAX_AMOUNT) {
            throw new InvalidArgumentException('VND amount exceeds the supported database range.');
        }
    }

    private static function assertQuantityRange(int $quantity): void
    {
        if ($quantity < 0 || $quantity > self::MAX_QUANTITY) {
            throw new InvalidArgumentException('Quantity exceeds the supported range.');
        }
    }
}
