<?php

declare(strict_types=1);

namespace Acme\Offer;

use Acme\Discount;

final readonly class Offers
{
    /** @var list<Offer> */
    private array $offers;

    public function __construct(Offer ...$offers)
    {
        $this->offers = $offers;
    }

    /**
     * @param list<\Acme\Product> $items
     * @return list<Discount>
     */
    public function apply(array $items): array
    {
        $applied = [];

        foreach ($this->offers as $offer) {
            $discount = $offer->apply($items);

            if ($discount->amount->isZero()) {
                continue;
            }

            $applied[] = $discount;
        }

        return $applied;
    }

    /**
     * @return list<string>
     */
    public function descriptions(): array
    {
        return array_map(
            static fn (Offer $offer): string => $offer->description(),
            $this->offers,
        );
    }
}
