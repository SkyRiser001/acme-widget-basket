<?php

declare(strict_types=1);

namespace Acme\Tests;

use Acme\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testDollarsAreStoredAsCents(): void
    {
        self::assertSame(3295, Money::dollars('32.95')->cents());
        self::assertSame('$32.95', Money::dollars('32.95')->format());
        self::assertSame('$0.00', Money::zero()->format());
    }

    public function testArithmeticStaysInWholeCents(): void
    {
        $total = Money::dollars('7.95')->plus(Money::dollars('24.95'));

        self::assertSame(3290, $total->cents());
        self::assertSame('$15.90', Money::dollars('7.95')->multipliedBy(2)->format());
    }

    public function testItRefusesToGoNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::dollars('1.00')->minus(Money::dollars('1.01'));
    }

    public function testItRejectsAmountsThatAreNotTwoDecimalPlaces(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::dollars('32.9');
    }
}
