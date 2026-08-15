<?php

namespace Tests\Feature\Procurement;

use App\Support\Money\VndMoney;
use InvalidArgumentException;
use Tests\TestCase;

class VndMoneyTest extends TestCase
{
    public function test_vnd_money_multiplies_whole_and_fractional_quantities_without_float_arithmetic(): void
    {
        $this->assertSame(37_000_000, VndMoney::multiplyByQuantity(18_500_000, '2'));
        $this->assertSame(15_000, VndMoney::multiplyByQuantity(10_000, '1.500'));
        $this->assertSame(3_330, VndMoney::multiplyByQuantity(10_001, '0.333'));
    }

    public function test_vnd_money_accepts_decimal_database_strings_only_when_fraction_is_zero(): void
    {
        $this->assertSame(18_500_000, VndMoney::toInteger('18500000.00'));

        $this->expectException(InvalidArgumentException::class);
        VndMoney::toInteger('18500000.50');
    }

    public function test_vnd_money_rejects_calculations_that_exceed_database_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        VndMoney::multiplyByQuantity(VndMoney::MAX_AMOUNT, '1.001');
    }

    public function test_vnd_money_rejects_totals_that_exceed_database_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        VndMoney::add(VndMoney::MAX_AMOUNT, 1);
    }
}
