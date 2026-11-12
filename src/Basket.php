<?php

declare(strict_types=1);

namespace Acme;

use Acme\Delivery\DeliveryChargeRule;
use Acme\Offer\Offers;
use LogicException;

final class Basket
{
    /** @var list<Product> */
    private array $items = [];

    public function __construct(
        private Catalogue $catalogue,
        private DeliveryChargeRule $delivery,
        private Offers $offers,
    ) {
    }

    public function add(string $productCode): void
    {
        $this->items[] = $this->catalogue->get($productCode);
    }

    public function total(): Money
    {
        return $this->summary()->total;
    }

    public function summary(): BasketSummary
    {
        // An empty basket is not an order, so it does not pick up the $4.95 band.
        if ($this->items === []) {
            return BasketSummary::empty();
        }

        $lines = $this->groupLines();
        $subtotal = Money::zero();

        foreach ($lines as $line) {
            $subtotal = $subtotal->plus($line->lineTotal);
        }

        $discounts = $this->offers->apply($this->items);
        $discountTotal = Money::zero();

        foreach ($discounts as $discount) {
            $discountTotal = $discountTotal->plus($discount->amount);
        }

        if ($discountTotal->cents() > $subtotal->cents()) {
            throw new LogicException('Offers discounted more than the value of the goods.');
        }

        $goodsTotal = $subtotal->minus($discountTotal);
        // After the offer. Two red widgets fall under $50 here, so delivery is $4.95.
        $delivery = $this->delivery->chargeFor($goodsTotal);

        return new BasketSummary(
            $lines,
            $subtotal,
            $discounts,
            $discountTotal,
            $goodsTotal,
            $delivery,
            $goodsTotal->plus($delivery),
        );
    }

    /**
     * @return list<BasketLine>
     */
    private function groupLines(): array
    {
        /** @var array<string, array{product: Product, quantity: int}> $groups */
        $groups = [];
        $order = [];

        foreach ($this->items as $item) {
            $key = $item->code . ':' . $item->price->cents();

            if (!isset($groups[$key])) {
                $order[] = $key;
                $groups[$key] = ['product' => $item, 'quantity' => 0];
            }

            $groups[$key]['quantity']++;
        }

        $lines = [];

        foreach ($order as $key) {
            $product = $groups[$key]['product'];
            $quantity = $groups[$key]['quantity'];
            $lines[] = new BasketLine(
                $product->code,
                $product->name,
                $quantity,
                $product->price,
                $product->price->multipliedBy($quantity),
            );
        }

        return $lines;
    }
}
