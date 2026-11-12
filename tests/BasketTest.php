<?php

declare(strict_types=1);

namespace Acme\Tests;

use Acme\AcmeWidgetCo;
use Acme\Basket;
use Acme\Delivery\DeliveryChargeRule;
use Acme\Discount;
use Acme\Money;
use Acme\Offer\Offer;
use Acme\Offer\Offers;
use Acme\UnknownProductException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BasketTest extends TestCase
{
    #[DataProvider('sampleBaskets')]
    public function testTheSampleBaskets(array $codes, string $expected): void
    {
        self::assertSame($expected, $this->basket($codes)->total()->format());
    }

    /**
     * @return array<string, array{0: list<string>, 1: string}>
     */
    public static function sampleBaskets(): array
    {
        return [
            'B01, G01' => [['B01', 'G01'], '$37.85'],
            'R01, R01' => [['R01', 'R01'], '$54.37'],
            'R01, G01' => [['R01', 'G01'], '$60.85'],
            'B01, B01, R01, R01, R01' => [['B01', 'B01', 'R01', 'R01', 'R01'], '$98.27'],
        ];
    }

    public function testTwoRedWidgetsMoveIntoTheHigherDeliveryBand(): void
    {
        $summary = $this->basket(['R01', 'R01'])->summary();

        self::assertSame(6590, $summary->subtotal->cents());
        self::assertSame(1648, $summary->discountTotal->cents());
        self::assertSame(4942, $summary->goodsTotal->cents());
        self::assertSame(495, $summary->delivery->cents());
        self::assertSame(5437, $summary->total->cents());
        self::assertSame(
            'Buy one red widget, get the second half price',
            $summary->discounts[0]->description,
        );
    }

    public function testThreeRedWidgetsAndTwoBlueOnesAreFreeToDeliver(): void
    {
        $summary = $this->basket(['B01', 'B01', 'R01', 'R01', 'R01'])->summary();

        self::assertSame(['B01', 'R01'], array_map(static fn ($line) => $line->code, $summary->lines));
        self::assertSame([2, 3], array_map(static fn ($line) => $line->quantity, $summary->lines));
        self::assertSame(1590, $summary->lines[0]->lineTotal->cents());
        self::assertSame(9885, $summary->lines[1]->lineTotal->cents());
        self::assertSame(11475, $summary->subtotal->cents());
        self::assertSame(1648, $summary->discountTotal->cents());
        self::assertSame(9827, $summary->goodsTotal->cents());
        self::assertSame(0, $summary->delivery->cents());
        self::assertSame('$98.27', $summary->total->format());
    }

    public function testAnEmptyBasketHasNoDeliveryCharge(): void
    {
        self::assertSame('$0.00', AcmeWidgetCo::basket()->total()->format());
    }

    public function testAnUnknownProductIsRejected(): void
    {
        $this->expectException(UnknownProductException::class);

        AcmeWidgetCo::basket()->add('NOPE');
    }

    public function testProductCodesAreNotCaseSensitive(): void
    {
        self::assertSame('$37.85', $this->basket(['b01', ' g01 '])->total()->format());
    }

    public function testTheDeliveryRuleIsInjected(): void
    {
        $basket = new Basket(
            (new AcmeWidgetCo())->configure()->catalogue,
            new class implements DeliveryChargeRule {
                public function chargeFor(Money $goodsTotal): Money
                {
                    return Money::dollars('10.00');
                }
            },
            new Offers(),
        );

        self::assertSame('$17.95', $this->basket(['B01'], $basket)->total()->format());
    }

    public function testANonEmptyBasketStillPaysDeliveryWhenTheGoodsAreFree(): void
    {
        $basket = new Basket(
            (new AcmeWidgetCo())->configure()->catalogue,
            (new AcmeWidgetCo())->configure()->delivery,
            new Offers(new class implements Offer {
                public function description(): string
                {
                    return 'Staff discount';
                }

                public function apply(array $items): Discount
                {
                    $sum = Money::zero();

                    foreach ($items as $item) {
                        $sum = $sum->plus($item->price);
                    }

                    return new Discount('Staff discount', $sum);
                }
            }),
        );

        self::assertSame('$4.95', $this->basket(['B01'], $basket)->total()->format());
    }

    /**
     * @param list<string> $codes
     */
    private function basket(array $codes, ?Basket $basket = null): Basket
    {
        $basket ??= AcmeWidgetCo::basket();

        foreach ($codes as $code) {
            $basket->add($code);
        }

        return $basket;
    }
}
