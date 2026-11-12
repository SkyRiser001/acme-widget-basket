<?php

declare(strict_types=1);

namespace Acme;

use Acme\Delivery\DeliveryChargeRule;
use Acme\Delivery\DeliveryTier;
use Acme\Offer\Offers;

final readonly class SalesConfig
{
    /**
     * @param list<DeliveryTier> $deliveryTiers
     */
    public function __construct(
        public Catalogue $catalogue,
        public DeliveryChargeRule $delivery,
        public Offers $offers,
        public array $deliveryTiers,
    ) {
    }

    public function basket(): Basket
    {
        return new Basket($this->catalogue, $this->delivery, $this->offers);
    }
}
