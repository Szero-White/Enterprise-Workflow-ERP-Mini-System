<?php

namespace Tests\Unit\Support;

use App\Support\QuantityFormatter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class QuantityFormatterTest extends TestCase
{
    #[Test]
    public function it_removes_redundant_decimal_zeroes(): void
    {
        $this->assertSame('0', QuantityFormatter::format('0.000'));
        $this->assertSame('1', QuantityFormatter::format('1.000'));
        $this->assertSame('2', QuantityFormatter::format('2.000'));
    }

    #[Test]
    public function it_preserves_meaningful_fractional_quantities(): void
    {
        $this->assertSame('1.5', QuantityFormatter::format('1.500'));
        $this->assertSame('1.25', QuantityFormatter::format('1.250'));
        $this->assertSame('0.125', QuantityFormatter::format('0.125'));
    }
}
