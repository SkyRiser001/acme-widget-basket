<?php

declare(strict_types=1);

namespace Acme;

use Acme\Delivery\DeliveryTier;
use Acme\Delivery\TieredDeliveryRule;
use Acme\Offer\Offers;
use Acme\Offer\SecondItemHalfPriceOffer;

// Prices, delivery bands, and the live offer. The basket does not know these numbers.
final class AcmeWidgetCo
{
    public static function basket(): Basket
    {
        return (new self())->configure()->basket();
    }

    public function configure(): SalesConfig
    {
        $catalogue = new InMemoryCatalogue([
            new Product('R01', 'Red Widget', Money::dollars('32.95')),
            new Product('G01', 'Green Widget', Money::dollars('24.95')),
            new Product('B01', 'Blue Widget', Money::dollars('7.95')),
        ]);

        $deliveryTiers = [
            new DeliveryTier(Money::dollars('50.00'), Money::dollars('4.95')),
            new DeliveryTier(Money::dollars('90.00'), Money::dollars('2.95')),
            new DeliveryTier(null, Money::zero()),
        ];

        $offers = new Offers(
            new SecondItemHalfPriceOffer(
                'R01',
                'Buy one red widget, get the second half price',
            ),
        );

        return new SalesConfig(
            catalogue: $catalogue,
            delivery: new TieredDeliveryRule(...$deliveryTiers),
            offers: $offers,
            deliveryTiers: $deliveryTiers,
        );
    }
}
