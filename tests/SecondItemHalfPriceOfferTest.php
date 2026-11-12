<?php

declare(strict_types=1);

namespace Acme\Tests;

use Acme\Money;
use Acme\Offer\SecondItemHalfPriceOffer;
use Acme\Product;
use PHPUnit\Framework\TestCase;

final class SecondItemHalfPriceOfferTest extends TestCase
{
    public function testASingleWidgetIsFullPrice(): void
    {
        self::assertSame(0, $this->offer()->apply([$this->red()])->amount->cents());
    }

    public function testTheSecondRedWidgetDropsTheHalfCent(): void
    {
        self::assertSame(1648, $this->offer()->apply([$this->red(), $this->red()])->amount->cents());
    }

    public function testAnOddWidgetStaysAtFullPrice(): void
    {
        $reds = [$this->red(), $this->red(), $this->red()];

        self::assertSame(1648, $this->offer()->apply($reds)->amount->cents());
    }

    public function testEveryPairIsDiscounted(): void
    {
        $reds = [$this->red(), $this->red(), $this->red(), $this->red()];

        self::assertSame(3296, $this->offer()->apply($reds)->amount->cents());
    }

    public function testOtherProductsAreIgnored(): void
    {
        $green = new Product('G01', 'Green Widget', Money::dollars('24.95'));

        self::assertSame(0, $this->offer()->apply([$green, $green])->amount->cents());
    }

    public function testTheCheaperWidgetInThePairIsTheOneDiscounted(): void
    {
        $offer = new SecondItemHalfPriceOffer('R01', 'Half price');
        $expensive = new Product('R01', 'Red Widget', Money::dollars('10.00'));
        $cheap = new Product('R01', 'Red Widget', Money::dollars('8.00'));

        self::assertSame(400, $offer->apply([$expensive, $cheap])->amount->cents());
    }

    private function offer(): SecondItemHalfPriceOffer
    {
        return new SecondItemHalfPriceOffer('R01', 'Buy one red widget, get the second half price');
    }

    private function red(): Product
    {
        return new Product('R01', 'Red Widget', Money::dollars('32.95'));
    }
}
