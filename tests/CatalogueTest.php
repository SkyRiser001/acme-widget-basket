<?php

declare(strict_types=1);

namespace Acme\Tests;

use Acme\InMemoryCatalogue;
use Acme\Money;
use Acme\Product;
use Acme\UnknownProductException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CatalogueTest extends TestCase
{
    public function testCodesAreMatchedWithoutCaseOrSurroundingSpace(): void
    {
        $catalogue = new InMemoryCatalogue([
            new Product('r01', 'Red Widget', Money::dollars('32.95')),
        ]);

        self::assertSame('R01', $catalogue->get(' r01 ')->code);
        self::assertSame('Red Widget', $catalogue->get('R01')->name);
    }

    public function testAnUnknownCodeIsRejected(): void
    {
        $catalogue = new InMemoryCatalogue([
            new Product('R01', 'Red Widget', Money::dollars('32.95')),
        ]);

        $this->expectException(UnknownProductException::class);
        $catalogue->get('NOPE');
    }

    public function testDuplicateCodesAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new InMemoryCatalogue([
            new Product('R01', 'Red Widget', Money::dollars('32.95')),
            new Product('R01', 'Another red', Money::dollars('10.00')),
        ]);
    }
}
