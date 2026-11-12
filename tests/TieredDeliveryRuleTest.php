<?php

declare(strict_types=1);

namespace Acme\Tests;

use Acme\AcmeWidgetCo;
use Acme\Delivery\DeliveryTier;
use Acme\Delivery\TieredDeliveryRule;
use Acme\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TieredDeliveryRuleTest extends TestCase
{
    #[DataProvider('acmeBands')]
    public function testAcmeBandsAreExclusiveAtTheTop(string $goods, int $chargeCents): void
    {
        $rule = (new AcmeWidgetCo())->configure()->delivery;

        self::assertSame($chargeCents, $rule->chargeFor(Money::dollars($goods))->cents());
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function acmeBands(): array
    {
        return [
            'nothing' => ['0.00', 495],
            'just under $50' => ['49.99', 495],
            'exactly $50' => ['50.00', 295],
            'just under $90' => ['89.99', 295],
            'exactly $90' => ['90.00', 0],
            'above $90' => ['114.75', 0],
        ];
    }

    public function testTheBandsComeFromTheTiersItIsGiven(): void
    {
        $rule = new TieredDeliveryRule(
            new DeliveryTier(Money::dollars('10.00'), Money::dollars('3.00')),
            new DeliveryTier(null, Money::zero()),
        );

        self::assertSame(300, $rule->chargeFor(Money::dollars('9.99'))->cents());
        self::assertSame(0, $rule->chargeFor(Money::dollars('10.00'))->cents());
    }

    public function testTheLastTierMustCoverEveryRemainingTotal(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TieredDeliveryRule(
            new DeliveryTier(Money::dollars('50.00'), Money::dollars('4.95')),
        );
    }

    public function testTiersMustClimb(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TieredDeliveryRule(
            new DeliveryTier(Money::dollars('90.00'), Money::dollars('2.95')),
            new DeliveryTier(Money::dollars('50.00'), Money::dollars('4.95')),
            new DeliveryTier(null, Money::zero()),
        );
    }
}
