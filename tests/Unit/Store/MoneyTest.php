<?php
declare(strict_types=1);
namespace Tests\Unit\Store;
use PHPUnit\Framework\TestCase;
use App\Services\Store\Money;
use DomainException;

final class MoneyTest extends TestCase
{
    public function testConversionAndFormatting():void
    {
        self::assertSame(1299000000,Money::cents('12990000.00'));
        self::assertSame('12990000.00',Money::decimal(1299000000));
        self::assertSame('12.990.000 đ',Money::format(1299000000));
    }
    public function testInvalidMoneyIsRejected():void
    {
        $this->expectException(DomainException::class);
        Money::cents('-1');
    }
    public function testVatIncludedIsComputedWithoutDoubleCharging():void
    {
        self::assertSame(1000,Money::includedTaxCents(11000,'10.00',true));
        self::assertSame(0,Money::includedTaxCents(11000,'10.00',false));
    }
}
